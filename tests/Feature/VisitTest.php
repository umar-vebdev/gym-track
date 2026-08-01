<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Memberships\Models\MembershipType;
use App\Modules\Visits\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_check_in_client_with_active_days_membership(): void
    {
        $client = Client::factory()->create();
        $purchase = MembershipPurchase::factory()->create([
            'client_id'  => $client->id,
            'starts_at'  => now()->subDays(5)->toDateString(),
            'expires_at' => now()->addDays(25)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/visits', [
            'client_id' => $client->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.client_id', $client->id)
            ->assertJsonPath('data.membership_purchase_id', $purchase->id);

        $this->assertDatabaseHas('visits', [
            'client_id'              => $client->id,
            'membership_purchase_id' => $purchase->id,
        ]);
    }

    public function test_check_in_decrements_visits_left_for_visits_based_membership(): void
    {
        $client = Client::factory()->create();
        $type = MembershipType::factory()->visits(10)->create();
        $purchase = MembershipPurchase::factory()->create([
            'client_id'          => $client->id,
            'membership_type_id' => $type->id,
            'starts_at'          => now()->subDays(1)->toDateString(),
            'expires_at'         => null,
            'visits_left'        => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/visits', [
            'client_id' => $client->id,
        ]);

        $response->assertStatus(201);

        // Остаток визитов должен уменьшиться до 9
        $this->assertDatabaseHas('membership_purchases', [
            'id'          => $purchase->id,
            'visits_left' => 9,
        ]);
    }

    public function test_cannot_check_in_client_without_active_membership(): void
    {
        $client = Client::factory()->create();

        // Просроченный абонемент
        MembershipPurchase::factory()->create([
            'client_id'  => $client->id,
            'starts_at'  => now()->subDays(40)->toDateString(),
            'expires_at' => now()->subDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/visits', [
            'client_id' => $client->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'CHECK_IN_FAILED');
    }

    public function test_can_list_visits(): void
    {
        Visit::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->getJson('/api/visits');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.items');
    }
}
