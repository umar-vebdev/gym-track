<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Memberships\Models\MembershipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_active_membership_types(): void
    {
        MembershipType::factory()->create(['is_active' => true, 'name' => 'Активный']);
        MembershipType::factory()->create(['is_active' => false, 'name' => 'Неактивный']);

        // По умолчанию только активные
        $response = $this->actingAs($this->user)->getJson('/api/membership-types');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        // Все тарифы
        $responseAll = $this->actingAs($this->user)->getJson('/api/membership-types?all=true');
        $responseAll->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_can_create_membership_type(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/membership-types', [
            'name'           => 'Месячный безлимит',
            'duration_type'  => 'days',
            'duration_value' => 30,
            'price'          => 2500.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Месячный безлимит')
            ->assertJsonPath('data.price', 2500);

        $this->assertDatabaseHas('membership_types', [
            'name'          => 'Месячный безлимит',
            'duration_type' => 'days',
        ]);
    }

    public function test_can_toggle_membership_type_active_status(): void
    {
        $type = MembershipType::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->user)->patchJson("/api/membership-types/{$type->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('membership_types', [
            'id'        => $type->id,
            'is_active' => false,
        ]);
    }
}
