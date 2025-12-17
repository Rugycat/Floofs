<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;

class PetController extends Controller
{
    /**
     * Display a listing of all pets.
     */
    public function index()
    {
        return response()->json(Pet::all(), 200);
    }

    /**
     * Store a newly created pet.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'species' => 'required|string|max:100',
        'breed' => 'nullable|string|max:100',
        'age' => 'required|integer|min:0',
        'photo_path' => 'nullable|string',
    ]);
    
    // Automatically associate with the authenticated user
    $validated['user_id'] = auth()->id();
    
    $pet = Pet::create($validated);
    return response()->json($pet, 201);
}

    /**
     * Display the specified pet.
     */
    public function show(Pet $pet)
    {
        return response()->json($pet, 200);
    }

    /**
     * Update the specified pet.
     */
    public function update(Request $request, Pet $pet)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'species' => 'sometimes|required|string|max:100',
            'age' => 'sometimes|required|integer|min:0',
        ]);

        $pet->update($validated);
        return response()->json($pet, 200);
    }

    /**
     * Remove the specified pet.
     */
    public function destroy(Pet $pet)
    {
        $pet->delete();
        return response()->json(null, 204);
    }
}
