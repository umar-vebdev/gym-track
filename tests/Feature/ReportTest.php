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
        
        $client = Client::factory()->create();
        $product = \App\Modules\Products\Models\Product::create(['name' => 'W', 'price' => 100, 'is_active' => true]);
        \App\Modules\Products\Models\ProductSale::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'total_price' => 100,
            'payment_method' => 'cash',
        ]);

        $today = now()->toDateString();
        $response = $this->actingAs($this->user)->getJson("/api/reports/revenue?start_date={$today}&end_date={$today}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_revenue', 3100)
            ->assertJsonPath('data.total_purchases', 2);
            
        // Test filtering by type
        $response = $this->actingAs($this->user)->getJson("/api/reports/revenue?start_date={$today}&end_date={$today}&type=product");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_revenue', 100);
    }
    
    public function test_can_get_history(): void
    {
        $client = Client::factory()->create();
        Visit::factory()->create(['client_id' => $client->id]);
        MembershipPurchase::factory()->create(['client_id' => $client->id]);
        
        $response = $this->actingAs($this->user)->getJson("/api/reports/history");
        
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.items');
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
