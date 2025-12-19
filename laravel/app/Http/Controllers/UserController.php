<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     * Tik admins gali matyti visus vartotojus
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();
        return response()->json($users, 200);
    }

    /**
     * Store a newly created user.
     * Tik admins gali kurti naujus vartotojus
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:user,admin,vet'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = $validated['role'] ?? 'user';
        
        $newUser = User::create($validated);
        
        return response()->json([
            'id' => $newUser->id,
            'name' => $newUser->name,
            'email' => $newUser->email,
            'role' => $newUser->role
        ], 201);
    }

    /**
     * Display the specified user.
     * Admins gali matyti bet kurį, vartotojai - tik save
     */
    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->role !== 'admin' && $currentUser->id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ], 200);
    }

    /**
     * Update the specified user.
     * Admins gali keisti visus, vartotojai - tik save
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Admins gali keisti visus, vartotojai tik save
        if ($currentUser->role !== 'admin' && $currentUser->id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Paprastai vartotojai negali keisti savo role
        if ($currentUser->role !== 'admin' && $request->has('role')) {
            return response()->json(['error' => 'Cannot change role'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'sometimes|nullable|string|in:user,admin,vet'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ], 200);
    }

    /**
     * Remove the specified user.
     * Tik admins gali trinti vartotojus
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Admins negali trinti save
        if ($currentUser->id === $user->id) {
            return response()->json(['error' => 'Cannot delete yourself'], 400);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}