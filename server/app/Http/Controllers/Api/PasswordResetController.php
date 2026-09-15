<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // Step 1 — Student submits email
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Always return success even if email not found — security best practice
        if (!$user) {
            return response()->json([
                'message' => 'If this email exists, a reset link has been sent.',
            ]);
        }

        // Delete any existing tokens for this user
        PasswordResetToken::where('user_id', $user->id)->delete();

        // Generate token
        $token    = Str::random(64);
        $expiresAt = now()->addMinutes(60);

        PasswordResetToken::create([
            'user_id'    => $user->id,
            'token'      => $token,
            'expires_at' => $expiresAt,
            'used'       => false,
        ]);

        // Build reset URL pointing to frontend
        $frontendUrl = config('app.frontend_url');
        if (empty($frontendUrl) || str_contains($frontendUrl, 'localhost') || str_contains($frontendUrl, '127.0.0.1')) {
            $origin = $request->header('Origin');
            if (!empty($origin)) {
                $frontendUrl = $origin;
            } elseif ($referer = $request->header('Referer')) {
                $parsed = parse_url($referer);
                if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
                    $port = !empty($parsed['port']) ? ':' . $parsed['port'] : '';
                    $frontendUrl = "{$parsed['scheme']}://{$parsed['host']}{$port}";
                }
            }
        }
        $frontendUrl = rtrim($frontendUrl ?: env('FRONTEND_URL', 'http://localhost:5174'), '/');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token;

        // Send email
        Mail::to($user->email)->send(new ForgotPasswordMail($resetUrl, $user->name));

        return response()->json([
            'message' => 'If this email exists, a reset link has been sent.',
        ]);
    }

    // Helper — Verify token status
    public function verifyToken(Request $request)
    {
        $token = $request->query('token') ?? $request->input('token');

        if (!$token) {
            return response()->json([
                'valid' => false,
                'message' => 'Reset token is required.',
            ], 400);
        }

        $tokenRecord = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired reset link.',
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token is valid.',
        ]);
    }

    // Step 2 — Student submits new password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tokenRecord = PasswordResetToken::where('token', $request->token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'message' => 'Invalid or expired reset link.',
            ], 400);
        }

        $user = User::find($tokenRecord->user_id);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $tokenRecord->update(['used' => true]);

        return response()->json([
            'message' => 'Password reset successfully. You can now log in.',
        ]);
    }
}
