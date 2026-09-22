<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $schoolName = fake()->company() . ' Academy';

        return [
            'name'             => $schoolName,
            'slug'             => \Illuminate\Support\Str::slug($schoolName),
            'email'            => fake()->unique()->companyEmail(),
            'phone'            => fake()->phoneNumber(),
            'address'          => fake()->address(),
            'logo'             => null,
            'status'           => 'active',
            'subscription_plan' => fake()->randomElement(['free', 'basic', 'premium']),
        ];
    }
}
