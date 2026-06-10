<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate a user and issue a per-device Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $deviceName = $request->input('device_name', 'api');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'tenant_id']),
            'tenant' => $user->tenant?->only(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Register a new tenant and its first admin user, returning a Sanctum token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'tenant_name' => 'required|string|max:255',
            'tenant_slug' => 'required|string|max:255|unique:tenants,slug',
            'device_name' => 'nullable|string|max:255',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'name' => $validated['tenant_name'],
                'slug' => $validated['tenant_slug'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'tenant_id' => $tenant->id,
            ]);

            return [$tenant, $user];
        });

        [$tenant, $user] = $result;

        $deviceName = $validated['device_name'] ?? 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'tenant_id']),
            'tenant' => $tenant->only(['id', 'name', 'slug']),
        ], 201);
    }

    /**
     * Return the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'tenant_id']),
            'tenant' => $user->tenant?->only(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Revoke the current access token (this device only).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
