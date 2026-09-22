<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'tenant_id'        => Tenant::factory(),
            'first_name'       => fake()->firstName(),
            'last_name'        => fake()->lastName(),
            'admission_number' => 'SCH/' . fake()->year() . '/' . fake()->unique()->numerify('####'),
            'class_id'         => SchoolClass::factory(),
            'section_id'       => Section::factory(),
            'gender'           => fake()->randomElement(['Male', 'Female']),
            'dob'              => fake()->dateTimeBetween('-18 years', '-5 years'),
            'blood_group'      => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'guardian_name'    => fake()->name(),
            'guardian_phone'   => fake()->phoneNumber(),
            'guardian_address' => fake()->address(),
            'status'           => 'active',
            'password'         => Hash::make('student123'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
