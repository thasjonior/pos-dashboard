<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiCompanyDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_legacy_keys_and_new_companies_array(): void
    {
        // Sateki and Kimuje come from the seed migration; just add Isotas
        Company::create(['name' => 'ISOTAS', 'location' => 'Temeke', 'slug' => 'isotas']);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Main aggregate always present
        $this->assertArrayHasKey('main', $data);
        $this->assertArrayHasKey('today_total_transactions', $data);

        // Backward-compat: all three slugs as top-level keys
        $this->assertArrayHasKey('sateki', $data);
        $this->assertArrayHasKey('kimuje', $data);
        $this->assertArrayHasKey('isotas', $data);

        // Backward-compat machine count keys (all lowercase)
        $this->assertArrayHasKey('sateki_machine_count', $data);
        $this->assertArrayHasKey('kimuje_machine_count', $data);
        $this->assertArrayHasKey('isotas_machine_count', $data);

        // New shape: companies array with all three
        $this->assertArrayHasKey('companies', $data);
        $this->assertCount(3, $data['companies']);

        $slugs = array_column($data['companies'], 'slug');
        $this->assertContains('sateki', $slugs);
        $this->assertContains('kimuje', $slugs);
        $this->assertContains('isotas', $slugs);

        // Each company entry has expected keys
        foreach ($data['companies'] as $company) {
            $this->assertArrayHasKey('id', $company);
            $this->assertArrayHasKey('slug', $company);
            $this->assertArrayHasKey('name', $company);
            $this->assertArrayHasKey('summary', $company);
            $this->assertArrayHasKey('machine_count', $company);
        }

        // The old capital-K typo key must NOT be present
        $this->assertArrayNotHasKey('Kimuje_machine_count', $data);
    }

    public function test_new_company_appears_without_code_changes(): void
    {
        Company::create(['name' => 'ISOTAS', 'location' => 'Temeke', 'slug' => 'isotas']);

        $data = $this->getJson('/api/dashboard')->json('data');

        $this->assertArrayHasKey('isotas', $data);
        $this->assertArrayHasKey('isotas_machine_count', $data);

        $slugs = array_column($data['companies'], 'slug');
        $this->assertContains('isotas', $slugs);
    }
}
