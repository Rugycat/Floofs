<?php
namespace App\Http\Controllers;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
class HealthRecordController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Vets and admins see all health records, regular users see only their pets' records
        if ($user->role === 'vet' || $user->role === 'admin') {
            $records = HealthRecord::with('pet')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $records = HealthRecord::whereHas('pet', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('pet')
            ->orderBy('created_at', 'desc')
            ->get();
        }
        
        return response()->json($records, 200);
    }
    
    public function store(Request $request)
    {
        // Only vets and admins can create health records
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can create health records'], 403);
        }
        
        $validated = $request->validate([
            'pet_id' => 'required|integer|exists:pets,id',
            'weight' => 'nullable|numeric',
            'vaccines' => 'nullable|string',
            'illness_history' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);
        
        $record = HealthRecord::create($validated);
        return response()->json($record, 201);
    }
    
    public function show(HealthRecord $healthRecord)
    {
        return response()->json($healthRecord, 200);
    }
    
    public function update(Request $request, HealthRecord $healthRecord)
    {
        // Only vets and admins can update health records
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can update health records'], 403);
        }
        
        $validated = $request->validate([
            'weight' => 'nullable|numeric',
            'vaccines' => 'nullable|string',
            'illness_history' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);
        
        $healthRecord->update($validated);
        return response()->json($healthRecord, 200);
    }
    
    public function destroy(HealthRecord $healthRecord)
    {
        // Only vets and admins can delete health records
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can delete health records'], 403);
        }
        
        $healthRecord->delete();
        return response()->json(null, 204);
    }
    
    public function indexForPet($petId)
    {
        $records = HealthRecord::where('pet_id', $petId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($records, 200);
    }
    
    public function storeForPet(Request $request, $petId)
    {
        // Only vets and admins can create health records
        $user = auth()->user();
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Only vets can create health records'], 403);
        }
        
        $request->merge(['pet_id' => $petId]);
        return $this->store($request);
    }
}