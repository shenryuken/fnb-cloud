<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Authenticate a user and return a token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Generate or update token
        $token = Str::random(80);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'tenant_id']),
            'tenant' => $user->tenant?->only(['id', 'name', 'slug'])
        ]);
    }

    /**
     * Register a new tenant and its first admin user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255', // User name
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'tenant_name' => 'required|string|max:255',
            'tenant_slug' => 'required|string|max:255|unique:tenants,slug',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // 1. Create Tenant
                $tenant = Tenant::create([
                    'name' => $validated['tenant_name'],
                    'slug' => $validated['tenant_slug'],
                ]);

                // 2. Create User
                $token = Str::random(80);
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'tenant_id' => $tenant->id,
                    'api_token' => $token,
                ]);

                return response()->json([
                    'token' => $token,
                    'user' => $user->only(['id', 'name', 'email', 'tenant_id']),
                    'tenant' => $tenant->only(['id', 'name', 'slug'])
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log the user out (invalidate the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
