<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\VideoProgress;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated user.
     *
     * Returns:
     *  - coursesEnrolled    → total enrollment count
     *  - lessonsCompleted   → completed VideoProgress rows
     *  - watchTimeSeconds   → sum of watch_time_seconds across all progress rows
     */
    public function stats()
    {
        $user = Auth::user();

        $coursesEnrolled = Enrollment::where('user_id', $user->id)->count();

        $lessonsCompleted = VideoProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $watchTimeSeconds = VideoProgress::where('user_id', $user->id)
            ->sum('watch_time_seconds');

        return response()->json([
            'success' => true,
            'stats'   => [
                'coursesEnrolled'  => $coursesEnrolled,
                'lessonsCompleted' => $lessonsCompleted,
                'watchTimeSeconds' => (int) $watchTimeSeconds,
            ],
        ]);
    }
}
