<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'required|string|max:255',
        ]);

        // Check if email already exists using find query
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => 'There is a validation error'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country,
        ]);

        $user->assignRole('user');

        // Create a Passport personal access token
        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country' => $user->country,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create a Passport personal access token
        $token = $user->createToken('auth_token')->accessToken;

        $roles = $user->getRoleNames();
        $role = $roles->isEmpty() ? null : $roles->first();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country' => $user->country,
                'role' => $role,
            ]
        ], 200);
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Get authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country' => $user->country,
                'role' => $user->role,
            ]
        ], 200);
    }

    /**
     * Update authenticated user's profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $user->update($validated);

        $responseUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'country' => $user->country,
            'role' => $user->role,
        ];

        $responseData = [
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $responseUser,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Update authenticated user's password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $currentPassword = $request->input('current_password');
        $isValidPassword = Hash::check($currentPassword, $user->password);

        if (!$isValidPassword) {
            $responseData = [
                'success' => false,
                'message' => 'Validation errors',
                'errors' => [
                    'current_password' => ['Current password is incorrect']
                ]
            ];

            return response()->json($responseData, 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        $responseData = [
            'success' => true,
            'message' => 'Password updated successfully'
        ];

        return response()->json($responseData, 200);
    }
}
