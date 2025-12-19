<?php
namespace App\Http\Controllers;
use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Http\Request;

class HealthRecordController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali matyti visus health records
        if ($user->role === 'vet' || $user->role === 'admin') {
            return response()->json(HealthRecord::all(), 200);
        }
        
        // Paprastai vartotojai matys tik savo augintinių health records
        $userPetIds = Pet::where('user_id', $user->id)->pluck('id');
        $records = HealthRecord::whereIn('pet_id', $userPetIds)->get();
        return response()->json($records, 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali kurti health records
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Tik veterinarai gali įvesti sveikatos duomenis'], 403);
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
        $user = auth()->user();
        
        // Check if user can view this record
        if ($user->role === 'user') {
            $pet = $healthRecord->pet;
            if ($pet->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        return response()->json($healthRecord, 200);
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali keisti health records
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
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
        $user = auth()->user();
        
        // Tik vets ir admins gali trinti health records
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $healthRecord->delete();
        return response()->json(null, 204);
    }

    public function indexForPet($petId)
    {
        $user = auth()->user();
        $pet = Pet::find($petId);
        
        if (!$pet) {
            return response()->json(['error' => 'Pet not found'], 404);
        }
        
        // Check if user can access this pet's data
        if ($user->role === 'user' && $pet->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $records = HealthRecord::where('pet_id', $petId)->get();
        return response()->json($records, 200);
    }

    public function storeForPet(Request $request, $petId)
    {
        $user = auth()->user();
        
        // Tik vets ir admins gali kurti
        if ($user->role !== 'vet' && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $pet = Pet::find($petId);
        if (!$pet) {
            return response()->json(['error' => 'Pet not found'], 404);
        }

        $request->merge(['pet_id' => $petId]);
        return $this->store($request);
    }
}