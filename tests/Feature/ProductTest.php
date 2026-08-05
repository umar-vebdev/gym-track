<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Clients\Models\Client;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_products(): void
    {
        Product::create(['name' => 'Water', 'price' => 50, 'is_active' => true]);
        Product::create(['name' => 'Protein', 'price' => 200, 'is_active' => false]);

        // Default list (only active)
        $response = $this->actingAs($this->user)->getJson('/api/products');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items');

        // All products
        $response = $this->actingAs($this->user)->getJson('/api/products?all=true');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_create_product(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/products', [
            'name' => 'Energy Drink',
            'price' => 150,
            'category' => 'drinks',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Energy Drink');

        $this->assertDatabaseHas('products', [
            'name' => 'Energy Drink',
            'price' => 150,
        ]);
    }

    public function test_can_sell_product_to_client(): void
    {
        $client = Client::factory()->create();
        $product = Product::create(['name' => 'Water', 'price' => 50, 'is_active' => true]);

        $response = $this->actingAs($this->user)->postJson('/api/product-sales', [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'payment_method' => 'card',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_price', 100);

        $this->assertDatabaseHas('product_sales', [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 100,
        ]);
    }
}
