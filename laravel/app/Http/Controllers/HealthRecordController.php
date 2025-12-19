<?php
namespace App\Http\Controllers;

use App\Models\HealthRecord;
use Illuminate\Http\Request;

class HealthRecordController extends Controller
{
    /**
     * Display all health records for authorized users
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'vet' || $user->role === 'admin') {
            // Vets and admins see all records
            return response()->json(HealthRecord::all(), 200);
        } else {
            // Regular users see only their own pets' health records
            $records = HealthRecord::whereIn('pet_id', 
                \App\Models\Pet::where('user_id', $user->id)->pluck('id')
            )->get();
            return response()->json($records, 200);
        }
    }

    /**
     * Create a new health record (only vets and admins)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Only vets and admins can create health records
        if ($user->role === 'user') {
            return response()->json(['error' => 'Only vets can create health records'], 403);
        }

        // Validate input
        $validated = $request->validate([
            'pet_id' => 'required|integer|exists:pets,id',
            'weight' => 'nullable|numeric|min:0',
            'vaccines' => 'nullable|string',
            'illness_history' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        try {
            $record = HealthRecord::create($validated);
            return response()->json($record, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create health record: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified health record
     */
    public function show(HealthRecord $healthRecord)
    {
        $user = auth()->user();
        
        // Check permissions
        if ($user->role === 'user') {
            $pet = $healthRecord->pet;
            if ($pet->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        return response()->json($healthRecord, 200);
    }

    /**
     * Update the specified health record
     */
    public function update(Request $request, HealthRecord $healthRecord)
    {
        $user = auth()->user();
        
        // Only vets and admins can update
        if ($user->role === 'user') {
            return response()->json(['error' => 'Only vets can update health records'], 403);
        }

        $validated = $request->validate([
            'pet_id' => 'sometimes|required|integer|exists:pets,id',
            'weight' => 'nullable|numeric|min:0',
            'vaccines' => 'nullable|string',
            'illness_history' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        try {
            $healthRecord->update($validated);
            return response()->json($healthRecord, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update health record: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete the specified health record
     */
    public function destroy(HealthRecord $healthRecord)
    {
        $user = auth()->user();
        
        // Only vets and admins can delete
        if ($user->role === 'user') {
            return response()->json(['error' => 'Only vets can delete health records'], 403);
        }

        try {
            $healthRecord->delete();
            return response()->json(['message' => 'Health record deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete health record: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get health records for a specific pet
     */
    public function indexForPet($petId)
    {
        $user = auth()->user();
        $pet = \App\Models\Pet::find($petId);

        if (!$pet) {
            return response()->json(['error' => 'Pet not found'], 404);
        }

        // Check permissions
        if ($user->role === 'user' && $pet->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $records = \App\Models\HealthRecord::where('pet_id', $petId)->get();
        return response()->json($records, 200);
    }

    /**
     * Create health record for a specific pet
     */
    public function storeForPet(Request $request, $petId)
    {
        $user = auth()->user();
        
        // Only vets and admins can create
        if ($user->role === 'user') {
            return response()->json(['error' => 'Only vets can create health records'], 403);
        }

        $pet = \App\Models\Pet::find($petId);
        if (!$pet) {
            return response()->json(['error' => 'Pet not found'], 404);
        }

        $request->merge(['pet_id' => $petId]);
        return $this->store($request);
    }
}