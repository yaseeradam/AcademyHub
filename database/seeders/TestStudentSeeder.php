<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Section;

class TestStudentSeeder extends Seeder
{
    public function run(): void
    {
        // Get first available class and section
        $class = SchoolClass::first();
        $section = Section::where('class_id', $class->id)->first();

        // Create test student
        Student::create([
            'admission_number' => 'STU20240001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'class_id' => $class->id,
            'section_id' => $section?->id,
            'gender' => 'Male',
            'dob' => '2010-05-15',
            'blood_group' => 'O+',
            'guardian_name' => 'Mr. Robert Doe',
            'guardian_phone' => '+1234567890',
            'guardian_address' => '123 Main Street, City',
            'status' => 'Active',
        ]);

        $this->command->info('✅ Test student created successfully!');
        $this->command->info('');
        $this->command->info('📝 Login Credentials:');
        $this->command->info('   Admission Number: STU20240001');
        $this->command->info('   Password: john0001');
        $this->command->info('');
        $this->command->info('🌐 Login at: /login (Click Student tab)');
    }
}
