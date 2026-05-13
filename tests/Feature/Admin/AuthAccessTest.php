<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    }

    private function collector(): User
    {
        return User::factory()->create(['role' => 'collector', 'is_active' => true, 'machine_name' => 'Testco001']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/machines')->assertRedirect('/login');
        $this->get('/admin/companies')->assertRedirect('/login');
    }

    public function test_admin_can_access_general_admin_routes(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_collector_login_is_rejected_via_web(): void
    {
        $collector = $this->collector();

        $response = $this->post('/login', [
            'email'    => $collector->email,
            'password' => 'password',
        ]);

        // Should be logged out immediately
        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_admin_cannot_access_super_admin_routes(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/audit-logs')
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_super_admin_can_access_all_admin_routes(): void
    {
        $sa = $this->superAdmin();

        $this->actingAs($sa)->get('/admin/audit-logs')->assertOk();
        $this->actingAs($sa)->get('/admin/users')->assertOk();
        $this->actingAs($sa)->get('/admin/dashboard')->assertOk();
    }
}
