<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Visits\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_dashboard_overview(): void
    {
        $client = Client::factory()->create();
        MembershipPurchase::factory()->create(['amount_paid' => 2500.00]);
        Visit::factory()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->user)->getJson('/api/reports/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'date',
                    'today_revenue',
                    'today_purchases_count',
                    'today_visits_count',
                    'total_clients_count',
                    'active_memberships',
                ],
            ]);
    }

    public function test_can_get_revenue_report(): void
    {
        MembershipPurchase::factory()->create([
            'amount_paid'    => 3000.00,
            'payment_method' => 'card',
            'created_at'     => now(),
        ]);

        $today = now()->toDateString();
        $response = $this->actingAs($this->user)->getJson("/api/reports/revenue?start_date={$today}&end_date={$today}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_revenue', 3000)
            ->assertJsonPath('data.total_purchases', 1);
    }

    public function test_can_get_visits_report(): void
    {
        Visit::factory()->create(['visited_at' => now()]);

        $today = now()->toDateString();
        $response = $this->actingAs($this->user)->getJson("/api/reports/visits?start_date={$today}&end_date={$today}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_visits', 1);
    }

    public function test_can_get_expiring_memberships(): void
    {
        MembershipPurchase::factory()->create([
            'expires_at' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/reports/expiring-memberships?days=7');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }
}
