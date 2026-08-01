<?php

namespace Database\Factories;

use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Memberships\Models\MembershipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipPurchase>
 */
class MembershipPurchaseFactory extends Factory
{
    protected $model = MembershipPurchase::class;

    public function definition(): array
    {
        return [
            'client_id'          => Client::factory(),
            'membership_type_id' => MembershipType::factory(),
            'amount_paid'        => 2500.00,
            'starts_at'          => now()->toDateString(),
            'expires_at'         => now()->addDays(30)->toDateString(),
            'visits_left'        => null,
            'payment_method'     => 'cash',
        ];
    }
}
