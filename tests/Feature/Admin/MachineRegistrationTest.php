<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Location;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    }

    private function seedCompanyAndLocation(): array
    {
        $company  = Company::create(['name' => 'TESTCO', 'slug' => 'testco']);
        $location = Location::create(['name' => 'Ilala Boma', 'slug' => 'ilala-boma']);
        return [$company, $location];
    }

    private function payload(Company $company, Location $location): array
    {
        return [
            'company_id'         => $company->id,
            'serial_number'      => 'TC-001',
            'name'               => 'Testco001',
            'location_id'        => $location->id,
            'type'               => 'mobile',
            'status'             => 'active',
            'collector_name'     => 'John Doe',
            'collector_phone'    => '+255700000001',
            'collector_password' => 'SecurePass123!',
            'installation_date'  => '2026-01-01',
        ];
    }

    public function test_creates_machine_and_collector_in_transaction(): void
    {
        [$company, $location] = $this->seedCompanyAndLocation();

        $this->actingAs($this->superAdmin())
            ->post('/admin/machines', $this->payload($company, $location))
            ->assertRedirect();

        $this->assertDatabaseHas('machines', ['name' => 'Testco001', 'company_id' => $company->id]);
        $this->assertDatabaseHas('users', ['name' => 'John Doe', 'role' => 'collector', 'machine_name' => 'Testco001']);
    }

    public function test_audit_log_entry_created_on_machine_registration(): void
    {
        [$company, $location] = $this->seedCompanyAndLocation();

        $this->actingAs($this->superAdmin())
            ->post('/admin/machines', $this->payload($company, $location));

        $machine = Machine::where('name', 'Testco001')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'created',
            'auditable_type' => Machine::class,
            'auditable_id'   => $machine->id,
        ]);
    }

    public function test_unique_machine_name_is_enforced(): void
    {
        [$company, $location] = $this->seedCompanyAndLocation();
        $payload = $this->payload($company, $location);

        $this->actingAs($this->superAdmin())->post('/admin/machines', $payload);

        // Second attempt with same machine name
        $this->actingAs($this->superAdmin())
            ->post('/admin/machines', array_merge($payload, ['serial_number' => 'TC-002']))
            ->assertSessionHasErrors('name');
    }

    public function test_cannot_delete_machine_with_collections(): void
    {
        [$company, $location] = $this->seedCompanyAndLocation();
        $machine = Machine::create([
            'name'          => 'Testco099',
            'serial_number' => 'TC-099',
            'is_active'     => true,
            'company_id'    => $company->id,
        ]);

        // Create a collection referencing this machine
        \App\Models\Collection::create([
            'machine_id' => $machine->id,
            'receipt_id' => 'TEST-DEL-001',
            'amount'     => 100,
            'date'       => now()->toDateString(),
        ]);

        $this->actingAs($this->superAdmin())
            ->delete("/admin/machines/{$machine->id}")
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('machines', ['id' => $machine->id]);
    }
}
