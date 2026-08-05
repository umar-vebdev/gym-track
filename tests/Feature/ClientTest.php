<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Clients\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_clients_with_pagination(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.items');
    }

    public function test_can_search_clients_by_name_phone_or_code(): void
    {
        $client = Client::factory()->create([
            'full_name'   => 'Иванов Пётр',
            'phone'       => '+7 999 111-22-33',
            'client_code' => 'GT-0001',
        ]);
        Client::factory()->create(['full_name' => 'Сидоров Алексей']);

        // Поиск по имени
        $response = $this->actingAs($this->user)->getJson('/api/clients?search=Иванов');
        $response->assertStatus(200)->assertJsonCount(1, 'data.items');

        // Поиск по коду
        $response = $this->actingAs($this->user)->getJson('/api/clients?search=GT-0001');
        $response->assertStatus(200)->assertJsonCount(1, 'data.items');
    }

    public function test_can_create_client_with_auto_generated_code(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/clients', [
            'full_name' => 'Сергеев Иван',
            'phone'     => '+7 900 123-45-67',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Сергеев Иван')
            ->assertJsonPath('data.client_code', 'GT-0001');

        $this->assertDatabaseHas('clients', [
            'full_name'   => 'Сергеев Иван',
            'client_code' => 'GT-0001',
        ]);
    }

    public function test_cannot_create_client_with_duplicate_phone(): void
    {
        Client::factory()->create(['phone' => '+7 999 111-22-33']);

        $response = $this->actingAs($this->user)->postJson('/api/clients', [
            'full_name' => 'Новый Клиент',
            'phone'     => '+7 999 111-22-33',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_can_update_client(): void
    {
        $client = Client::factory()->create(['full_name' => 'Старое Имя']);

        $response = $this->actingAs($this->user)->putJson("/api/clients/{$client->id}", [
            'full_name' => 'Новое Имя',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.full_name', 'Новое Имя');
    }

    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_can_filter_clients_by_membership_type(): void
    {
        $client1 = Client::factory()->create();
        $membershipType1 = \App\Modules\Memberships\Models\MembershipType::factory()->create();
        \App\Modules\Memberships\Models\MembershipPurchase::factory()->create([
            'client_id' => $client1->id,
            'membership_type_id' => $membershipType1->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $client2 = Client::factory()->create();
        $membershipType2 = \App\Modules\Memberships\Models\MembershipType::factory()->create();
        \App\Modules\Memberships\Models\MembershipPurchase::factory()->create([
            'client_id' => $client2->id,
            'membership_type_id' => $membershipType2->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/clients?membership_type_id={$membershipType1->id}");

        $response->assertStatus(200)->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $client1->id);
    }

    public function test_can_deduct_visit_from_active_membership(): void
    {
        $client = Client::factory()->create();
        $membershipType = \App\Modules\Memberships\Models\MembershipType::factory()->create(['duration_type' => 'visits', 'duration_value' => 10]);
        $purchase = \App\Modules\Memberships\Models\MembershipPurchase::factory()->create([
            'client_id' => $client->id,
            'membership_type_id' => $membershipType->id,
            'starts_at' => now()->subDay()->toDateString(),
            'expires_at' => null,
            'visits_left' => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/clients/{$client->id}/deduct-visit");

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('membership_purchases', [
            'id' => $purchase->id,
            'visits_left' => 9,
        ]);
        
        $this->assertDatabaseHas('visits', [
            'client_id' => $client->id,
            'membership_purchase_id' => $purchase->id,
        ]);
    }
}
