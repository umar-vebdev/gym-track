<?php

namespace Database\Factories;

use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Visits\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'client_id'              => Client::factory(),
            'membership_purchase_id' => MembershipPurchase::factory(),
            'visited_at'             => now(),
        ];
    }
}
