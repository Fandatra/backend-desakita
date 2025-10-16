<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /users
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        return User::all();
    }

    // POST /users
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    // GET /users/{id}
    public function show(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        return User::findOrFail($id);
    }

    // PUT /users/{id}
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role'     => 'sometimes|in:admin,user',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    // DELETE /users/{id}
    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        User::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    private function authorizeAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
