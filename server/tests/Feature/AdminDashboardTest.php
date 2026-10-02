<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys --force');
        $this->artisan('passport:client --personal --name=users --provider=users');
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_admin_dashboard()
    {
        $response = $this->getJson('/api/admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Passport::actingAs($user);

        $response = $this->getJson('/api/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_fetch_dashboard_data()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin);

        // Create some student users
        $student1 = User::factory()->create(['country' => 'US']);
        $student1->assignRole('user');
        $student2 = User::factory()->create(['country' => 'UK']);
        $student2->assignRole('user');

        // Create some courses
        $course1 = Course::create([
            'title' => 'Course 1',
            'slug' => 'course-1',
            'description' => 'Test course 1',
            'level' => 'beginner',
            'price' => 99.00,
            'is_published' => true,
        ]);

        $course2 = Course::create([
            'title' => 'Course 2',
            'slug' => 'course-2',
            'description' => 'Test course 2',
            'level' => 'intermediate',
            'price' => 149.00,
            'is_published' => true,
        ]);

        // Create some orders
        Order::create([
            'user_id' => $student1->id,
            'course_id' => $course1->id,
            'amount' => 99.00,
            'currency' => 'USD',
            'status' => 'finished',
            'paid_at' => now(),
        ]);

        Order::create([
            'user_id' => $student2->id,
            'course_id' => $course2->id,
            'amount' => 149.00,
            'currency' => 'USD',
            'status' => 'waiting',
        ]);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stats' => [
                    'totalStudents',
                    'totalCourses',
                    'totalRevenue',
                    'totalOrders',
                    'studentsTrend',
                    'coursesTrend',
                    'revenueTrend',
                    'ordersTrend',
                ],
                'recentOrders',
                'recentStudents',
            ]);

        $data = $response->json();
        $this->assertEquals(2, $data['stats']['totalStudents']);
        $this->assertEquals(2, $data['stats']['totalCourses']);
        $this->assertEquals(99.0, $data['stats']['totalRevenue']);
        $this->assertEquals(2, $data['stats']['totalOrders']);
        $this->assertCount(2, $data['recentOrders']);
        $this->assertCount(2, $data['recentStudents']);
    }
}
