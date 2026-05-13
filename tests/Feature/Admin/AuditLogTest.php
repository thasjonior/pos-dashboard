<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Location;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

    public function test_only_super_admin_can_view_audit_logs(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/audit-logs')
            ->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->get('/admin/audit-logs')
            ->assertOk();
    }

    public function test_company_creation_is_audit_logged(): void
    {
        $sa = $this->superAdmin();

        $this->actingAs($sa)
            ->post('/admin/companies', ['name' => 'LOGTEST', 'slug' => 'logtest']);

        $company = Company::where('slug', 'logtest')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'user_id'        => $sa->id,
            'action'         => 'created',
            'auditable_type' => Company::class,
            'auditable_id'   => $company->id,
        ]);
    }

    public function test_company_update_is_audit_logged_with_changes(): void
    {
        $company = Company::create(['name' => 'OLD', 'slug' => 'old-co']);
        $sa      = $this->superAdmin();

        $this->actingAs($sa)
            ->put("/admin/companies/{$company->id}", ['name' => 'NEW', 'slug' => 'old-co']);

        $log = AuditLog::where('auditable_type', Company::class)
            ->where('auditable_id', $company->id)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->changes);
    }

    public function test_company_delete_is_audit_logged(): void
    {
        $company = Company::create(['name' => 'DEL CO', 'slug' => 'del-co']);
        $sa      = $this->superAdmin();

        $this->actingAs($sa)->delete("/admin/companies/{$company->id}");

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'deleted',
            'auditable_type' => Company::class,
            'auditable_id'   => $company->id,
        ]);
    }

    public function test_location_changes_are_audit_logged(): void
    {
        $sa = $this->superAdmin();

        $this->actingAs($sa)->post('/admin/locations', ['name' => 'Test Boma', 'slug' => 'test-boma', 'is_active' => 1]);

        $location = Location::where('slug', 'test-boma')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'created',
            'auditable_type' => Location::class,
            'auditable_id'   => $location->id,
        ]);
    }

    public function test_audit_log_index_shows_entries(): void
    {
        $sa = $this->superAdmin();
        AuditLog::record($sa, 'created', Company::create(['name' => 'SHOW TEST', 'slug' => 'show-test']));

        $this->actingAs($sa)
            ->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee('created');
    }
}
