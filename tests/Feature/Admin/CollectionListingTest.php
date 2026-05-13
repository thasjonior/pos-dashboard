<?php

namespace Tests\Feature\Admin;

use App\Models\Collection;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionListingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function seedData(): array
    {
        $company = Company::create(['name' => 'TESTCO', 'slug' => 'testco']);
        $machine = Machine::create([
            'name'          => 'Testco001',
            'serial_number' => 'TC-001',
            'is_active'     => true,
            'company_id'    => $company->id,
        ]);
        return [$company, $machine];
    }

    public function test_collections_index_is_accessible(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/collections')
            ->assertOk();
    }

    public function test_date_range_filter_limits_results(): void
    {
        [, $machine] = $this->seedData();

        Collection::create(['machine_id' => $machine->id, 'receipt_id' => 'OLD-001', 'amount' => 100, 'date' => '2020-01-01']);
        Collection::create(['machine_id' => $machine->id, 'receipt_id' => 'NEW-001', 'amount' => 200, 'date' => now()->toDateString()]);

        $response = $this->actingAs($this->admin())
            ->get('/admin/collections?from=' . now()->toDateString() . '&to=' . now()->toDateString())
            ->assertOk();

        $response->assertSee('NEW-001');
        $response->assertDontSee('OLD-001');
    }

    public function test_company_filter_limits_results(): void
    {
        $company1 = Company::create(['name' => 'CO ONE', 'slug' => 'co-one']);
        $company2 = Company::create(['name' => 'CO TWO', 'slug' => 'co-two']);
        $machine1 = Machine::create(['name' => 'Co1-001', 'serial_number' => 'C1-001', 'is_active' => true, 'company_id' => $company1->id]);
        $machine2 = Machine::create(['name' => 'Co2-001', 'serial_number' => 'C2-001', 'is_active' => true, 'company_id' => $company2->id]);

        Collection::create(['machine_id' => $machine1->id, 'receipt_id' => 'C1-REC', 'amount' => 100, 'date' => now()->toDateString()]);
        Collection::create(['machine_id' => $machine2->id, 'receipt_id' => 'C2-REC', 'amount' => 200, 'date' => now()->toDateString()]);

        $response = $this->actingAs($this->admin())
            ->get("/admin/collections?company_id={$company1->id}&from=2000-01-01&to=2099-01-01")
            ->assertOk();

        $response->assertSee('C1-REC');
        $response->assertDontSee('C2-REC');
    }

    public function test_export_returns_csv_with_correct_headers(): void
    {
        [, $machine] = $this->seedData();
        Collection::create(['machine_id' => $machine->id, 'receipt_id' => 'EXP-001', 'amount' => 500, 'date' => now()->toDateString()]);

        $response = $this->actingAs($this->admin())
            ->get('/admin/collections/export?from=2000-01-01&to=2099-01-01');

        $response->assertOk();
        $this->assertStringContainsStringIgnoringCase('receipt', $response->getContent());
    }
}
