<?php
namespace App\Http\Controllers;
use App\Models\Procedure;
use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Vets ir admins matys visas procedures
        if ($user->role === 'vet' || $user->role === 'admin') {
            return response()->json(Procedure::all(), 200);
        }
        
        // Paprastai vartotojai matys tik savo augintinių procedures
        $userPetIds = Pet::where('user_id', $user->id)->pluck('id');
        $procedures = Procedure::whereIn('health_record_id', function ($query) use ($userPetIds) {
            $query->select('id')
                  ->from('health_records')
                  ->whereIn('pet_id', $userPetIds);
        })->get();
        
        return response()->json($procedures, 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali kurti procedures
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Tik veterinarai gali įvesti procedūras'], 403);
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
        $user = auth()->user();
        
        // Check if user can view this procedure
        if ($user->role === 'user') {
            $healthRecord = $procedure->healthRecord;
            $pet = $healthRecord->pet;
            if ($pet->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        return response()->json($procedure, 200);
    }

    public function update(Request $request, Procedure $procedure)
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali keisti procedures
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
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
        $user = auth()->user();
        
        // Tik vets ir admins gali trinti procedures
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $procedure->delete();
        return response()->json(null, 204);
    }

    public function indexForRecord($recordId)
    {
        $user = auth()->user();
        $healthRecord = HealthRecord::find($recordId);
        
        if (!$healthRecord) {
            return response()->json(['error' => 'Health record not found'], 404);
        }
        
        // Check if user can access this record's data
        if ($user->role === 'user' && $healthRecord->pet->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $list = Procedure::where('health_record_id', $recordId)->get();
        return response()->json($list, 200);
    }

    public function storeForRecord(Request $request, $recordId)
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali kurti
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $healthRecord = HealthRecord::find($recordId);
        if (!$healthRecord) {
            return response()->json(['error' => 'Health record not found'], 404);
        }

        $request->merge(['health_record_id' => $recordId]);
        return $this->store($request);
    }
}