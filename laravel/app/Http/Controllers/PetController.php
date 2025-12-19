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
        $user = auth()->user();
        
        // Vets ir admins matys visus augintiniais, paprastai vartotojai - tik savus
        if ($user->role === 'vet' || $user->role === 'admin') {
            $pets = Pet::all();
        } else {
            $pets = Pet::where('user_id', auth()->id())->get();
        }
        
        return response()->json($pets, 200);
    }

    /**
     * Store a newly created pet.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Tik users ir vets gali kurti augintiniais (admins gali per users API)
        if ($user->role === 'admin') {
            return response()->json(['error' => 'Admins cannot create pets directly'], 403);
        }

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
        $user = auth()->user();
        
        // Check if user can access this pet
        if ($user->role === 'user' && $pet->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($pet, 200);
    }

    /**
     * Update the specified pet.
     */
    public function update(Request $request, Pet $pet)
    {
        $user = auth()->user();
        
        // Check permissions
        if ($user->role === 'user' && $pet->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'species' => 'sometimes|required|string|max:100',
            'breed' => 'nullable|string|max:100',
            'age' => 'sometimes|required|integer|min:0',
            'photo_path' => 'nullable|string',
        ]);

        // Regular users cannot change owner
        if ($user->role === 'user' && $request->has('user_id')) {
            return response()->json(['error' => 'Cannot change pet owner'], 403);
        }

        $pet->update($validated);
        return response()->json($pet, 200);
    }

    /**
     * Remove the specified pet.
     */
    public function destroy(Pet $pet)
    {
        $user = auth()->user();
        
        // Check permissions
        if ($user->role === 'user' && $pet->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $pet->delete();
        return response()->json(null, 204);
    }
}