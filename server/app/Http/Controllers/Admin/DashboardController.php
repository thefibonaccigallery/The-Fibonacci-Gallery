<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get aggregate statistics and recent data for the admin dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $startOfMonth = now()->startOfMonth();
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();
        $startOfWeek = now()->startOfWeek();

        // 1. Total Students (excluding admins)
        $studentsQuery = User::whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']));
        $totalStudents = (clone $studentsQuery)->count();

        $studentsThisMonth = (clone $studentsQuery)->where('created_at', '>=', $startOfMonth)->count();
        $studentsLastMonth = (clone $studentsQuery)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        if ($studentsLastMonth > 0) {
            $percent = round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100);
            $studentsTrend = ($percent >= 0 ? "+{$percent}%" : "{$percent}%") . ' this month';
        } elseif ($studentsThisMonth > 0) {
            $studentsTrend = "+{$studentsThisMonth} this month";
        } else {
            $studentsTrend = '0% this month';
        }

        // 2. Total Courses
        $totalCourses = Course::count();
        $coursesThisMonth = Course::where('created_at', '>=', $startOfMonth)->count();
        $coursesTrend = "+{$coursesThisMonth} new this month";

        // 3. Total Revenue
        $totalRevenue = (float) Order::where('status', 'finished')->sum('amount');
        $revenueThisMonth = (float) Order::where('status', 'finished')->where('created_at', '>=', $startOfMonth)->sum('amount');
        $revenueLastMonth = (float) Order::where('status', 'finished')->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('amount');

        if ($revenueLastMonth > 0) {
            $percent = round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100);
            $revenueTrend = ($percent >= 0 ? "+{$percent}%" : "{$percent}%") . ' this month';
        } elseif ($revenueThisMonth > 0) {
            $revenueTrend = '+$' . number_format($revenueThisMonth, 0) . ' this month';
        } else {
            $revenueTrend = '0% this month';
        }

        // 4. Total Orders
        $totalOrders = Order::count();
        $ordersThisWeek = Order::where('created_at', '>=', $startOfWeek)->count();
        $ordersTrend = "+{$ordersThisWeek} this week";

        // 5. Recent Orders (Latest 5)
        $recentOrders = Order::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 6. Recent Students (Latest 5)
        $recentStudents = User::withCount('enrollments')
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalCourses' => $totalCourses,
                'totalRevenue' => $totalRevenue,
                'totalOrders' => $totalOrders,
                'studentsTrend' => $studentsTrend,
                'coursesTrend' => $coursesTrend,
                'revenueTrend' => $revenueTrend,
                'ordersTrend' => $ordersTrend,
            ],
            'recentOrders' => $recentOrders,
            'recentStudents' => $recentStudents,
        ]);
    }
}
