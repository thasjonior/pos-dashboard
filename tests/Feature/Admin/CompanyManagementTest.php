<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    }

    public function test_can_create_company_with_slug(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/companies', [
                'name' => 'ISOTAS',
                'slug' => 'isotas',
            ])
            ->assertRedirect('/admin/companies');

        $this->assertDatabaseHas('companies', ['name' => 'ISOTAS', 'slug' => 'isotas']);
        $this->assertSame(1, AuditLog::where('action', 'created')->whereJsonContains('changes->name', 'ISOTAS')->orWhere(function ($q) {
            $q->where('action', 'created')->where('auditable_type', 'like', '%Company%');
        })->count());
    }

    public function test_slug_is_auto_generated_from_name(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/companies', ['name' => 'My Test Co'])
            ->assertRedirect('/admin/companies');

        $this->assertDatabaseHas('companies', ['slug' => 'my-test-co']);
    }

    public function test_duplicate_name_is_rejected(): void
    {
        Company::create(['name' => 'ISOTAS', 'slug' => 'isotas']);

        $this->actingAs($this->superAdmin())
            ->post('/admin/companies', ['name' => 'ISOTAS', 'slug' => 'isotas2'])
            ->assertSessionHasErrors('name');
    }

    public function test_can_update_company(): void
    {
        $company = Company::create(['name' => 'OLD NAME', 'slug' => 'old-name']);

        $this->actingAs($this->superAdmin())
            ->put("/admin/companies/{$company->id}", [
                'name' => 'NEW NAME',
                'slug' => 'old-name',
            ])
            ->assertRedirect('/admin/companies');

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'NEW NAME']);
    }

    public function test_delete_blocked_when_company_has_machines(): void
    {
        $company = Company::create(['name' => 'WITH MACHINES', 'slug' => 'with-machines']);
        Machine::create([
            'name'          => 'Wm001',
            'serial_number' => 'WM-001',
            'is_active'     => true,
            'company_id'    => $company->id,
        ]);

        $this->actingAs($this->superAdmin())
            ->delete("/admin/companies/{$company->id}")
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_can_delete_company_with_no_machines(): void
    {
        $company = Company::create(['name' => 'EMPTY CO', 'slug' => 'empty-co']);

        $this->actingAs($this->superAdmin())
            ->delete("/admin/companies/{$company->id}")
            ->assertRedirect('/admin/companies');

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_audit_log_records_created_action(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/companies', ['name' => 'AUDITCO', 'slug' => 'auditco']);

        $company = Company::where('slug', 'auditco')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'created',
            'auditable_type' => Company::class,
            'auditable_id'   => $company->id,
        ]);
    }
}
