<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\CommunicationLog;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantGraph(User $user, Service $service, string $suffix): array
    {
        $client = Client::create([
            'user_id' => $user->id,
            'company_name' => "Client {$suffix}",
            'industry' => 'Technology',
            'address' => "Address {$suffix}",
            'notes' => "Notes {$suffix}",
        ]);

        $contact = Contact::create([
            'client_id' => $client->id,
            'name' => "Contact {$suffix}",
            'position' => 'Manager',
            'email' => "contact{$suffix}@example.com",
            'phone' => '08123456789',
        ]);

        $contract = Contract::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'contract_value' => 1000000,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'contract_id' => $contract->id,
            'amount' => 500000,
            'payment_date' => '2026-05-10',
            'payment_method' => 'transfer',
            'status' => 'paid',
        ]);

        $document = Document::create([
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'uploaded_by' => $user->id,
            'file_name' => "contract-{$suffix}.pdf",
            'file_path' => "/uploads/contract-{$suffix}.pdf",
            'document_type' => 'contract',
            'uploaded_at' => now(),
        ]);

        $log = CommunicationLog::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'communication_type' => 'meeting',
            'notes' => "Discussed {$suffix}",
            'communication_date' => '2026-05-11',
        ]);

        return compact('client', 'contact', 'contract', 'payment', 'document', 'log');
    }

    public function test_index_endpoints_only_return_authenticated_users_related_records(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $serviceA = Service::create([
            'user_id' => $userA->id,
            'name' => 'Website Development',
            'description' => 'Company profile',
            'base_price' => 1000000,
        ]);

        $serviceB = Service::create([
            'user_id' => $userB->id,
            'name' => 'Mobile App Development',
            'description' => 'Mobile package',
            'base_price' => 2000000,
        ]);

        $tenantA = $this->createTenantGraph($userA, $serviceA, 'a');
        $tenantB = $this->createTenantGraph($userB, $serviceB, 'b');

        Sanctum::actingAs($userA);

        $this->getJson('/api/clients')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['client']->id);

        $this->getJson('/api/contacts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['contact']->id);

        $this->getJson('/api/contracts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['contract']->id);

        $this->getJson('/api/payments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['payment']->id);

        $this->getJson('/api/documents')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['document']->id);

        $this->getJson('/api/communication-logs')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['log']->id);

        $this->getJson('/api/services')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $serviceA->id);

        $this->getJson("/api/services/{$serviceA->id}/contracts")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenantA['contract']->id);

        $this->assertNotSame($tenantA['contract']->id, $tenantB['contract']->id);
    }

    public function test_writes_reject_foreign_tenant_relationships(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $serviceA = Service::create([
            'user_id' => $userA->id,
            'name' => 'CRM Setup',
            'description' => 'Setup package',
            'base_price' => 2000000,
        ]);

        $serviceB = Service::create([
            'user_id' => $userB->id,
            'name' => 'Blocked Service',
            'description' => 'Other tenant package',
            'base_price' => 3000000,
        ]);

        $tenantA = $this->createTenantGraph($userA, $serviceA, 'a');
        $tenantB = $this->createTenantGraph($userB, $serviceB, 'b');

        Sanctum::actingAs($userA);

        $this->postJson('/api/contacts', [
            'client_id' => $tenantB['client']->id,
            'name' => 'Blocked Contact',
            'email' => 'blocked@example.com',
            'phone' => '08111111111',
        ])->assertUnprocessable();

        $this->postJson('/api/contracts', [
            'client_id' => $tenantB['client']->id,
            'service_id' => $serviceA->id,
            'contract_value' => 100000,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'status' => 'active',
        ])->assertUnprocessable();

        $this->postJson('/api/contracts', [
            'client_id' => $tenantA['client']->id,
            'service_id' => $serviceB->id,
            'contract_value' => 100000,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'status' => 'active',
        ])->assertUnprocessable();

        $this->postJson('/api/payments', [
            'contract_id' => $tenantB['contract']->id,
            'amount' => 100000,
            'payment_date' => '2026-05-15',
            'payment_method' => 'transfer',
            'status' => 'paid',
        ])->assertUnprocessable();

        $this->postJson('/api/documents', [
            'client_id' => $tenantA['client']->id,
            'contract_id' => $tenantB['contract']->id,
            'uploaded_by' => $userB->id,
            'file_name' => 'blocked.pdf',
            'file_path' => '/uploads/blocked.pdf',
            'document_type' => 'contract',
        ])->assertUnprocessable();

        $this->postJson('/api/communication-logs', [
            'client_id' => $tenantB['client']->id,
            'user_id' => $userB->id,
            'communication_type' => 'meeting',
            'communication_date' => '2026-05-15',
        ])->assertUnprocessable();
    }

    public function test_direct_access_to_foreign_tenant_records_is_forbidden(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $service = Service::create([
            'user_id' => $userB->id,
            'name' => 'Support',
            'description' => 'Monthly support',
            'base_price' => 500000,
        ]);

        $tenantB = $this->createTenantGraph($userB, $service, 'b');

        Sanctum::actingAs($userA);

        $this->getJson("/api/clients/{$tenantB['client']->id}")->assertForbidden();
        $this->getJson("/api/contacts/{$tenantB['contact']->id}")->assertForbidden();
        $this->getJson("/api/contracts/{$tenantB['contract']->id}")->assertForbidden();
        $this->getJson("/api/services/{$service->id}")->assertForbidden();
        $this->getJson("/api/payments/{$tenantB['payment']->id}")->assertForbidden();
        $this->getJson("/api/documents/{$tenantB['document']->id}")->assertForbidden();
        $this->getJson("/api/communication-logs/{$tenantB['log']->id}")->assertForbidden();

        $this->deleteJson("/api/contracts/{$tenantB['contract']->id}")->assertForbidden();
        $this->deleteJson("/api/payments/{$tenantB['payment']->id}")->assertForbidden();
        $this->deleteJson("/api/documents/{$tenantB['document']->id}")->assertForbidden();
    }

    public function test_document_creator_is_taken_from_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $service = Service::create([
            'user_id' => $userA->id,
            'name' => 'Documentation',
            'description' => 'Document package',
            'base_price' => 750000,
        ]);

        $tenantA = $this->createTenantGraph($userA, $service, 'a');

        Sanctum::actingAs($userA);

        $response = $this->postJson('/api/documents', [
            'client_id' => $tenantA['client']->id,
            'contract_id' => $tenantA['contract']->id,
            'uploaded_by' => $userB->id,
            'file_name' => 'owned.pdf',
            'file_path' => '/uploads/owned.pdf',
            'document_type' => 'contract',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.uploaded_by', $userA->id);
    }

    public function test_pending_payments_reserve_available_balance_but_do_not_reduce_display_remaining(): void
    {
        $user = User::factory()->create();

        $service = Service::create([
            'user_id' => $user->id,
            'name' => 'Implementation',
            'description' => 'Implementation package',
            'base_price' => 1000000,
        ]);

        $tenant = $this->createTenantGraph($user, $service, 'a');
        $tenant['contract']->update([
            'contract_value' => 1000000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/payments', [
            'contract_id' => $tenant['contract']->id,
            'amount' => 400000,
            'payment_date' => '2026-05-15',
            'payment_method' => 'transfer',
            'status' => 'pending',
        ])->assertCreated()
            ->assertJsonPath('meta.remaining_balance', 500000)
            ->assertJsonPath('meta.available_balance', 100000);

        $this->postJson('/api/payments', [
            'contract_id' => $tenant['contract']->id,
            'amount' => 100001,
            'payment_date' => '2026-05-16',
            'payment_method' => 'transfer',
            'status' => 'paid',
        ])->assertUnprocessable();

        $this->assertSame('active', $tenant['contract']->fresh()->status);
    }
}
