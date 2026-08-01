<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Memberships\Models\MembershipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_purchase_days_based_membership(): void
    {
        $client = Client::factory()->create();
        $type = MembershipType::factory()->create([
            'duration_type'  => 'days',
            'duration_value' => 30,
            'price'          => 3000.00,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/membership-purchases', [
            'client_id'          => $client->id,
            'membership_type_id' => $type->id,
            'starts_at'          => now()->toDateString(),
            'payment_method'     => 'cash',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount_paid', 3000)
            ->assertJsonPath('data.expires_at', now()->addDays(30)->subDay()->toDateString())
            ->assertJsonPath('data.is_active', true);
    }

    public function test_can_purchase_visits_based_membership(): void
    {
        $client = Client::factory()->create();
        $type = MembershipType::factory()->visits(12)->create(['price' => 2000.00]);

        $response = $this->actingAs($this->user)->postJson('/api/membership-purchases', [
            'client_id'          => $client->id,
            'membership_type_id' => $type->id,
            'amount_paid'        => 1800.00, // Со скидкой
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.visits_left', 12)
            ->assertJsonPath('data.amount_paid', 1800)
            ->assertJsonPath('data.expires_at', null);
    }

    public function test_can_list_purchases_by_client(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        MembershipPurchase::factory()->create(['client_id' => $client1->id]);
        MembershipPurchase::factory()->create(['client_id' => $client2->id]);

        $response = $this->actingAs($this->user)->getJson("/api/membership-purchases?client_id={$client1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.client_id', $client1->id);
    }
}
