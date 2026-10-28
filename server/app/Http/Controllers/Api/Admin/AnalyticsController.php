<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\OrderBy;
use Spatie\Analytics\Period;

class AnalyticsController extends Controller
{
    /**
     * Get Google Analytics 4 metrics for the admin dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Validate range parameter against allowed config values
        $allowedRanges = config('types_analytics.ranges', [
            'today',
            '7_days',
            '30_days',
            '90_days',
            '7 days',
            '30 days',
            '90 days',
        ]);

        $defaultRange = config('types_analytics.default_range', '30_days');

        $validated = $request->validate([
            'range' => ['nullable', 'string', Rule::in($allowedRanges)],
        ]);

        $rawRange = $validated['range'] ?? $defaultRange;
        $normalizedRange = str_replace(' ', '_', $rawRange);

        // 2. Validate credentials and configuration before hitting the API
        $credentialsPath = config('analytics.service_account_credentials_json');
        $propertyId = config('analytics.property_id');

        if (empty($propertyId) || empty($credentialsPath) || !file_exists($credentialsPath)) {
            Log::error('Google Analytics configuration or credentials file is missing.', [
                'property_id_set' => !empty($propertyId),
                'credentials_file_exists' => !empty($credentialsPath) && file_exists($credentialsPath),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Analytics service is currently unavailable. Please verify service configuration.',
            ], 503);
        }

        // 3. Cache response for 30 minutes per range
        $cacheKey = "admin_analytics_range_{$normalizedRange}";
        $cacheTtl = now()->addMinutes(30);

        try {
            $response = Cache::remember($cacheKey, $cacheTtl, function () use ($normalizedRange, $propertyId) {
                $period = $this->resolvePeriod($normalizedRange);

                // Fetch aggregate totals
                $totals = Analytics::get($period, ['activeUsers', 'screenPageViews']);
                $totalVisitors = $totals->isNotEmpty() ? (int) ($totals->first()['activeUsers'] ?? 0) : 0;
                $totalPageViews = $totals->isNotEmpty() ? (int) ($totals->first()['screenPageViews'] ?? 0) : 0;

                // Fetch daily breakdown for chart
                $dailyCollection = Analytics::fetchTotalVisitorsAndPageViews($period, 100);
                $dailyMetrics = $dailyCollection->map(function ($row) {
                    $dateStr = $row['date'] instanceof \DateTimeInterface
                        ? $row['date']->format('Y-m-d')
                        : (string) $row['date'];

                    return [
                        'date' => $dateStr,
                        'visitors' => (int) ($row['activeUsers'] ?? 0),
                        'pageViews' => (int) ($row['screenPageViews'] ?? 0),
                    ];
                })->sortBy('date')->values()->all();

                // If period totals returned 0 but daily records exist, fallback to sum
                if ($totalVisitors === 0 && count($dailyMetrics) > 0) {
                    $totalVisitors = array_sum(array_column($dailyMetrics, 'visitors'));
                }
                if ($totalPageViews === 0 && count($dailyMetrics) > 0) {
                    $totalPageViews = array_sum(array_column($dailyMetrics, 'pageViews'));
                }

                // Fetch top pages
                $pagesCollection = Analytics::fetchMostVisitedPages($period, 10);
                $topPages = $pagesCollection->map(function ($row) {
                    return [
                        'path' => $row['fullPageUrl'] ?? $row['pagePath'] ?? '',
                        'title' => $row['pageTitle'] ?? '',
                        'views' => (int) ($row['screenPageViews'] ?? 0),
                    ];
                })->values()->all();

                // Fetch top countries
                $countriesCollection = Analytics::fetchTopCountries($period, 10);
                $topCountries = $countriesCollection->map(function ($row) {
                    return [
                        'country' => $row['country'] ?? 'Unknown',
                        'visitors' => (int) ($row['screenPageViews'] ?? $row['activeUsers'] ?? 0),
                    ];
                })->values()->all();

                // Fetch top traffic sources
                $sourcesCollection = Analytics::get(
                    $period,
                    ['activeUsers'],
                    ['sessionSource'],
                    10,
                    [OrderBy::metric('activeUsers', true)]
                );
                $topSources = $sourcesCollection->map(function ($row) {
                    return [
                        'source' => $row['sessionSource'] ?? '(direct)',
                        'visitors' => (int) ($row['activeUsers'] ?? 0),
                    ];
                })->values()->all();

                // Fetch device split
                $deviceCollection = Analytics::get(
                    $period,
                    ['activeUsers'],
                    ['deviceCategory'],
                    10,
                    [OrderBy::metric('activeUsers', true)]
                );
                $deviceTotal = (int) $deviceCollection->sum('activeUsers');
                $deviceSplit = $deviceCollection->map(function ($row) use ($deviceTotal) {
                    $count = (int) ($row['activeUsers'] ?? 0);
                    $percentage = $deviceTotal > 0 ? round(($count / $deviceTotal) * 100, 1) : 0;

                    return [
                        'device' => strtolower($row['deviceCategory'] ?? 'other'),
                        'visitors' => $count,
                        'percentage' => $percentage,
                    ];
                })->values()->all();

                $hasData = ($totalVisitors > 0 || $totalPageViews > 0 || count($dailyMetrics) > 0 || count($topPages) > 0);

                return [
                    'success' => true,
                    'range' => $normalizedRange,
                    'data' => [
                        'totalVisitors' => $totalVisitors,
                        'totalPageViews' => $totalPageViews,
                        'dailyMetrics' => $dailyMetrics,
                        'topPages' => $topPages,
                        'topCountries' => $topCountries,
                        'topSources' => $topSources,
                        'deviceSplit' => $deviceSplit,
                        'hasData' => $hasData,
                        'propertyUrl' => "https://analytics.google.com/analytics/web/#/p{$propertyId}/reports/reportinghub",
                    ],
                ];
            });

            return response()->json($response);
        } catch (\Throwable $e) {
            // Log raw Google error server-side, never expose to client
            Log::error('Google Analytics Data API request failed: ' . $e->getMessage(), [
                'exception' => $e,
                'range' => $normalizedRange,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics data from Google. Please try again later.',
            ], 502);
        }
    }

    /**
     * Resolve Period object based on the requested range key.
     */
    protected function resolvePeriod(string $range): Period
    {
        return match ($range) {
            'today' => Period::create(Carbon::today()->startOfDay(), Carbon::today()->endOfDay()),
            '7_days' => Period::create(Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay()),
            '30_days' => Period::create(Carbon::today()->subDays(29)->startOfDay(), Carbon::today()->endOfDay()),
            '90_days' => Period::create(Carbon::today()->subDays(89)->startOfDay(), Carbon::today()->endOfDay()),
            default => Period::create(Carbon::today()->subDays(29)->startOfDay(), Carbon::today()->endOfDay()),
        };
    }
}
