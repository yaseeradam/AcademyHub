<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo parent accounts
        $parents = [
            [
                'name' => 'John Doe',
                'email' => 'parent1@myacademy.local',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'is_active' => true,
                'custom_fields' => ['phone' => '+234-801-234-5678'],
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'parent2@myacademy.local',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'is_active' => true,
                'custom_fields' => ['phone' => '+234-802-345-6789'],
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'parent3@myacademy.local',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'is_active' => true,
                'custom_fields' => ['phone' => '+234-803-456-7890'],
            ],
        ];

        foreach ($parents as $parentData) {
            $parent = User::query()->updateOrCreate(
                ['email' => $parentData['email']],
                $parentData
            );
            
            // Link random students to each parent (1-3 children per parent)
            $studentCount = rand(1, 3);
            $availableStudents = Student::query()->inRandomOrder()->limit($studentCount)->get();
            
            foreach ($availableStudents as $student) {
                // Check if student is not already linked to another parent
                if ($student->parents()->count() === 0) {
                    $parent->students()->attach($student->id);
                }
            }
        }

        $this->command->info('Demo parent accounts created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('- parent1@myacademy.local / password');
        $this->command->info('- parent2@myacademy.local / password');
        $this->command->info('- parent3@myacademy.local / password');
    }
}
