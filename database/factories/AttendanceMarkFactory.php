<?php

namespace Database\Factories;

use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceMark>
 */
class AttendanceMarkFactory extends Factory
{
    protected $model = AttendanceMark::class;

    public function definition(): array
    {
        return [
            'tenant_id'  => Tenant::factory(),
            'sheet_id'   => AttendanceSheet::factory(),
            'student_id' => Student::factory(),
            'status'     => fake()->randomElement(['Present', 'Late', 'Absent']),
            'note'       => fake()->optional(0.3)->sentence(),
        ];
    }

    public function present(): static
    {
        return $this->state(fn () => ['status' => 'Present']);
    }

    public function late(): static
    {
        return $this->state(fn () => ['status' => 'Late']);
    }

    public function absent(): static
    {
        return $this->state(fn () => ['status' => 'Absent']);
    }
}
