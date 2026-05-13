<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Collection;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function seedSearchFixtures(): void
    {
        $company = Company::create(['name' => 'TESTCO', 'slug' => 'testco']);
        $machine = Machine::create([
            'name'          => 'SearchMachine001',
            'serial_number' => 'SM-001',
            'is_active'     => true,
            'company_id'    => $company->id,
        ]);

        User::factory()->create(['name' => 'SearchCollector', 'role' => 'collector', 'phone' => '+255700999001', 'machine_name' => 'SearchMachine001']);
        Client::create(['name' => 'SearchClient']);
        Collection::create(['machine_id' => $machine->id, 'receipt_id' => 'SRCH-RECEIPT-001', 'amount' => 100, 'date' => now()->toDateString()]);
    }

    public function test_search_matches_machine_name(): void
    {
        $this->seedSearchFixtures();

        $this->actingAs($this->admin())
            ->get('/admin/search?q=SearchMachine')
            ->assertOk()
            ->assertSee('SearchMachine001');
    }

    public function test_search_matches_collector_name(): void
    {
        $this->seedSearchFixtures();

        $this->actingAs($this->admin())
            ->get('/admin/search?q=SearchCollector')
            ->assertOk()
            ->assertSee('SearchCollector');
    }

    public function test_search_matches_client_name(): void
    {
        $this->seedSearchFixtures();

        $this->actingAs($this->admin())
            ->get('/admin/search?q=SearchClient')
            ->assertOk()
            ->assertSee('SearchClient');
    }

    public function test_search_matches_receipt_id(): void
    {
        $this->seedSearchFixtures();

        $this->actingAs($this->admin())
            ->get('/admin/search?q=SRCH-RECEIPT-001')
            ->assertOk()
            ->assertSee('SRCH-RECEIPT-001');
    }

    public function test_empty_query_redirects_back(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/search?q=')
            ->assertRedirect();
    }

    public function test_json_format_returns_structured_data(): void
    {
        $this->seedSearchFixtures();

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/search?q=SearchMachine&format=json')
            ->assertOk();

        $response->assertJsonStructure(['machines', 'collectors', 'clients', 'collections']);
        $this->assertNotEmpty($response->json('machines'));
    }
}
