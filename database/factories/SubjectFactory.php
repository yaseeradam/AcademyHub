<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name'      => fake()->randomElement([
                'Mathematics', 'English Language', 'Basic Science', 'Social Studies',
                'Civic Education', 'Computer Science', 'Fine Arts', 'French',
                'Agricultural Science', 'Physical & Health Education', 'Business Studies',
                'Home Economics', 'Music', 'Yoruba', 'Igbo', 'Hausa',
                'Physics', 'Chemistry', 'Biology', 'Further Mathematics',
                'Economics', 'Government', 'Literature in English', 'Geography',
            ]),
        ];
    }
}
