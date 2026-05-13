<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnknownClientFallbackTest extends TestCase
{
    use RefreshDatabase;

    private function seedCompanyAndUser(): array
    {
        $company = Company::create([
            'name'     => 'TESTCO',
            'location' => 'Dar es Salaam',
            'slug'     => 'testco',
        ]);

        $user = User::create([
            'name'         => 'Test Collector',
            'email'        => 'collector@testco.com',
            'password'     => bcrypt('password'),
            'role'         => 'collector',
            'machine_name' => 'Testco001',
        ]);

        Machine::create([
            'name'          => 'Testco001',
            'serial_number' => 'TC-001',
            'is_active'     => true,
            'company_id'    => $company->id,
            'collector_id'  => $user->id,
        ]);

        return [$company, $user];
    }

    private function syncPayload(string $id, string $receiptNumber): array
    {
        return [
            'id'            => $id,
            'receiptNumber' => $receiptNumber,
            'totalAmount'   => 150.0,
            'clientName'    => 'Unknown',
            'clientPhone'   => null,
            'cashierName'   => 'Testco001',
            'items'         => [
                ['sourceName' => 'ADA YA TAKA', 'amount' => 150.0],
            ],
            'createdAt'     => now()->toISOString(),
            'metadata'      => [
                'collectorUsername' => 'Testco001',
                'loginMode'         => 'online',
            ],
        ];
    }

    public function test_sync_with_unknown_client_auto_creates_fallback_for_company(): void
    {
        [, $user] = $this->seedCompanyAndUser();
        $token = $user->createToken('CollectorApp')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/collection', $this->syncPayload('col_test_001', 'RCPT-TEST-001'));

        $response->assertStatus(200);

        // Fallback client must have been auto-created with company-slug-derived name
        $fallbackClient = Client::where('name', 'unknown-client-testco')->first();
        $this->assertNotNull($fallbackClient, 'Expected unknown-client-testco to be auto-created');

        // Collection must be attached to the fallback client
        $this->assertDatabaseHas('collections', [
            'receipt_id' => 'RCPT-TEST-001',
            'client_id'  => $fallbackClient->id,
        ]);
    }

    public function test_second_sync_reuses_existing_fallback_client(): void
    {
        [, $user] = $this->seedCompanyAndUser();
        $token = $user->createToken('CollectorApp')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/collection', $this->syncPayload('col_001', 'RCPT-001'))
            ->assertStatus(200);

        $this->withToken($token)
            ->postJson('/api/collection', $this->syncPayload('col_002', 'RCPT-002'))
            ->assertStatus(200);

        // Exactly one fallback client — must not be duplicated
        $this->assertSame(1, Client::where('name', 'unknown-client-testco')->count());
    }
}
