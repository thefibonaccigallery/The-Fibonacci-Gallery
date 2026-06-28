<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('enrollments')
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
            ->when($request->search, fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->country, fn($q) => $q->where('country', $request->country))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($query);
    }

    public function ban(Request $request, string $uuid)
    {
        $user = User::where('uuid', $uuid)
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
            ->firstOrFail();

        $user->tokens()->each(fn($token) => $token->revoke());

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student banned successfully.',
        ]);
    }

    public function unban(string $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student unbanned successfully.',
        ]);
    }
}
