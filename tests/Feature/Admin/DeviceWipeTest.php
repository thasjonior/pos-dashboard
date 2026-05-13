<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\DeviceCommand;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceWipeTest extends TestCase
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

    private function seedDevice(): DeviceCommand
    {
        $company = Company::create(['name' => 'TESTCO', 'slug' => 'testco']);
        $machine = Machine::create([
            'name'          => 'Testco001',
            'serial_number' => 'TC-001',
            'is_active'     => true,
            'company_id'    => $company->id,
        ]);

        return DeviceCommand::create([
            'device_id'    => 'device-abc-123',
            'machine_id'   => $machine->id,
            'wipe_command' => false,
        ]);
    }

    public function test_admin_cannot_trigger_wipe(): void
    {
        $device = $this->seedDevice();

        $this->actingAs($this->admin())
            ->post("/admin/devices/{$device->id}/wipe", [
                'confirm_device_id' => $device->device_id,
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_trigger_wipe_with_correct_confirmation(): void
    {
        $device = $this->seedDevice();

        $this->actingAs($this->superAdmin())
            ->post("/admin/devices/{$device->id}/wipe", [
                'confirm_device_id' => $device->device_id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('device_commands', [
            'id'           => $device->id,
            'wipe_command' => true,
        ]);
    }

    public function test_wipe_fails_with_wrong_device_id(): void
    {
        $device = $this->seedDevice();

        $this->actingAs($this->superAdmin())
            ->post("/admin/devices/{$device->id}/wipe", [
                'confirm_device_id' => 'wrong-id',
            ])
            ->assertSessionHasErrors('confirm_device_id');

        $this->assertDatabaseHas('device_commands', ['id' => $device->id, 'wipe_command' => false]);
    }

    public function test_wipe_is_audit_logged(): void
    {
        $device = $this->seedDevice();

        $this->actingAs($this->superAdmin())
            ->post("/admin/devices/{$device->id}/wipe", [
                'confirm_device_id' => $device->device_id,
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'wiped',
        ]);
    }
}
