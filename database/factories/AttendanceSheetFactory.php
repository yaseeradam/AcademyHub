<?php

namespace Database\Factories;

use App\Models\AttendanceSheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSheet>
 */
class AttendanceSheetFactory extends Factory
{
    protected $model = AttendanceSheet::class;

    public function definition(): array
    {
        return [
            'tenant_id'  => Tenant::factory(),
            'class_id'   => SchoolClass::factory(),
            'section_id' => Section::factory(),
            'date'       => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'term'       => fake()->randomElement([1, 2, 3]),
            'session'    => '2025/2026',
            'taken_by'   => User::factory(),
        ];
    }
}
