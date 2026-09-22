<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'class_id'  => SchoolClass::factory(),
            'name'      => fake()->randomElement(['A', 'B', 'C', 'Gold', 'Silver', 'Diamond']),
        ];
    }
}
