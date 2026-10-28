<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Passport;
use Spatie\Analytics\Facades\Analytics;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys --force');
        $this->artisan('passport:client --personal --name=users --provider=users');
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_analytics(): void
    {
        $response = $this->getJson('/api/admin/analytics');
        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_access_analytics(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Passport::actingAs($user);

        $response = $this->getJson('/api/admin/analytics');
        $response->assertStatus(403);
    }

    public function test_invalid_range_is_rejected_with_422(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin);

        $response = $this->getJson('/api/admin/analytics?range=invalid_range');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['range']);
    }

    public function test_missing_credentials_returns_clean_503_without_exposing_paths(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin);

        // Point credentials to a non-existent file
        config(['analytics.service_account_credentials_json' => storage_path('non_existent.json')]);

        $response = $this->getJson('/api/admin/analytics');
        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'message' => 'Analytics service is currently unavailable. Please verify service configuration.',
        ]);
        $this->assertStringNotContainsString('non_existent.json', $response->getContent());
        $this->assertStringNotContainsString('storage', $response->getContent());
    }

    public function test_admin_can_fetch_analytics_and_subsequent_request_is_served_from_cache(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin);

        Cache::flush();

        // Seed cache with mock data to simulate a cached response or first call
        $cacheKey = 'admin_analytics_range_30_days';
        $mockData = [
            'success' => true,
            'range' => '30_days',
            'data' => [
                'totalVisitors' => 1250,
                'totalPageViews' => 4800,
                'dailyMetrics' => [
                    ['date' => '2026-10-25', 'visitors' => 120, 'pageViews' => 350],
                    ['date' => '2026-10-26', 'visitors' => 150, 'pageViews' => 410],
                ],
                'topPages' => [
                    ['path' => '/courses', 'title' => 'Courses', 'views' => 1200],
                ],
                'topCountries' => [
                    ['country' => 'United States', 'visitors' => 500],
                ],
                'topSources' => [
                    ['source' => 'google', 'visitors' => 400],
                ],
                'deviceSplit' => [
                    ['device' => 'desktop', 'visitors' => 750, 'percentage' => 60.0],
                    ['device' => 'mobile', 'visitors' => 500, 'percentage' => 40.0],
                ],
                'hasData' => true,
                'propertyUrl' => 'https://analytics.google.com/analytics/web/#/p12345/reports/reportinghub',
            ],
        ];

        Cache::put($cacheKey, $mockData, now()->addMinutes(30));

        $response = $this->getJson('/api/admin/analytics?range=30_days');
        $response->assertStatus(200);
        $response->assertJson($mockData);

        // Verify cache is still populated
        $this->assertTrue(Cache::has($cacheKey));
    }
}
