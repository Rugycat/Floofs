<?php
namespace App\Http\Controllers;
use App\Models\Procedure;
use Illuminate\Http\Request;
class ProcedureController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $petId = $request->query('pet_id');
        $healthRecordId = $request->query('health_record_id');
        
        $query = Procedure::with('healthRecord.pet');
        
        // Vets and admins see all procedures
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            // Regular users see only procedures for their pets' health records
            $query->whereHas('healthRecord.pet', function($q) {
                $q->where('user_id', auth()->id());
            });
        }
        
        // Filter by health record if provided
        if ($healthRecordId) {
            $query->where('health_record_id', $healthRecordId);
        }
        
        // Filter by pet if provided
        if ($petId) {
            $query->whereHas('healthRecord', function($q) use ($petId) {
                $q->where('pet_id', $petId);
            });
        }
        
        $procedures = $query->orderBy('created_at', 'desc')->get();
        return response()->json($procedures, 200);
    }
    
    public function store(Request $request)
    {
        // Only vets and admins can create procedures
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can create procedures'], 403);
        }
        
        $validated = $request->validate([
            'health_record_id' => 'required|integer|exists:health_records,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'status' => 'nullable|in:planned,done,canceled'
        ]);
        
        $proc = Procedure::create($validated);
        return response()->json($proc, 201);
    }
    
    public function show(Procedure $procedure)
    {
        return response()->json($procedure, 200);
    }
    
    public function update(Request $request, Procedure $procedure)
    {
        // Only vets and admins can update procedures
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can update procedures'], 403);
        }
        
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'status' => 'nullable|in:planned,done,canceled'
        ]);
        
        $procedure->update($validated);
        return response()->json($procedure, 200);
    }
    
    public function destroy(Procedure $procedure)
    {
        // Only vets and admins can delete procedures
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can delete procedures'], 403);
        }
        
        $procedure->delete();
        return response()->json(null, 204);
    }
    
    // Hierarchical list: procedures for a given health record
    public function indexForRecord($recordId)
    {
        $list = Procedure::where('health_record_id', $recordId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($list, 200);
    }
    
    public function storeForRecord(Request $request, $recordId)
    {
        // Only vets and admins can create procedures
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can create procedures'], 403);
        }
        
        $request->merge(['health_record_id' => $recordId]);
        return $this->store($request);
    }
}