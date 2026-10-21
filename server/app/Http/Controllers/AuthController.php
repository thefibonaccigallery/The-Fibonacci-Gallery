<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $referredBy = null;
        $referredAt = null;

        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', trim($request->referral_code))->first();
            if ($referrer) {
                $referredBy = $referrer->id;
                $referredAt = now();
            }
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'country'       => $request->country,
            'country_code'  => $request->country_code,
            'phone_number'  => $request->phone_number,
            'referred_by'   => $referredBy,
            'referred_at'   => $referredAt,
        ]);

        $role = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $user->assignRole($role);

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->is_banned) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been banned.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ], 200);
    }

    public function user()
    {
        return response()->json([
            'success' => true,
            'user'    => new UserResource(Auth::user()),
        ], 200);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => new UserResource($user),
        ], 200);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => ['current_password' => ['Current password is incorrect']],
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }
}
