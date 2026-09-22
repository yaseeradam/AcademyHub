<?php

namespace Database\Factories;

use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Score>
 */
class ScoreFactory extends Factory
{
    protected $model = Score::class;

    public function definition(): array
    {
        return [
            'tenant_id'  => Tenant::factory(),
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'ca1'        => fake()->numberBetween(0, 10),
            'ca2'        => fake()->numberBetween(0, 10),
            'ca3'        => fake()->numberBetween(0, 10),
            'exam'       => fake()->numberBetween(0, 70),
            'term'       => fake()->randomElement([1, 2, 3]),
            'session'    => '2025/2026',
        ];
    }
}
