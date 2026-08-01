<?php

namespace Database\Factories;

use App\Modules\Memberships\Models\MembershipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipType>
 */
class MembershipTypeFactory extends Factory
{
    protected $model = MembershipType::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->word() . ' Абонемент',
            'duration_type'  => 'days',
            'duration_value' => 30,
            'price'          => 2500.00,
            'is_active'      => true,
        ];
    }

    public function visits(int $visits = 12): static
    {
        return $this->state(fn () => [
            'duration_type'  => 'visits',
            'duration_value' => $visits,
        ]);
    }
}
