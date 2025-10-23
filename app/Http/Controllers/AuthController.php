<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\HeadOfFamily;

class AuthController extends Controller
{
    /**
     * Register user baru
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|in:admin,user',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => $validated['role'] ?? 'user',
        ]);

        // otomatis buat entitas kepala keluarga
        $headOfFamily = null;
        if ($user->role === 'user') {
            $headOfFamily = HeadOfFamily::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'message' => 'Register success',
            'user'    => $user,
            'head_of_family' => $headOfFamily, // ← tambahkan ini
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = User::where('email', $credentials['email'])->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login success',
                'user'    => $user,
                'token'   => $token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Login failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout success',
        ]);
    }
}
