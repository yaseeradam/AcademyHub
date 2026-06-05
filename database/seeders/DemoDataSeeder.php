<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 teachers
        $teachers = [];
        $teacherNames = [
            'Mr. John Okafor', 'Mrs. Sarah Ahmed', 'Mr. David Eze', 'Mrs. Grace Adeyemi',
            'Mr. Peter Nwankwo', 'Mrs. Faith Okonkwo', 'Mr. Samuel Bello', 'Mrs. Joy Okoro',
            'Mr. Michael Ojo', 'Mrs. Blessing Uche'
        ];

        foreach ($teacherNames as $index => $name) {
            $email = 'teacher' . ($index + 1) . '@academyhub.local';
            $teachers[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'is_active' => true,
                ]
            );
        }

        // Get all classes and subjects
        $classes = SchoolClass::all();
        $subjects = Subject::all();

        // Allocate subjects to teachers
        foreach ($classes as $class) {
            foreach ($subjects as $index => $subject) {
                $teacher = $teachers[$index % count($teachers)];
                SubjectAllocation::firstOrCreate([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                ]);
            }
        }

        // Nigerian first names
        $firstNames = [
            'Chinedu', 'Amina', 'Oluwaseun', 'Fatima', 'Emeka', 'Zainab', 'Tunde', 'Aisha',
            'Chioma', 'Ibrahim', 'Ngozi', 'Musa', 'Adaeze', 'Yusuf', 'Chiamaka', 'Abdullahi',
            'Nneka', 'Usman', 'Ifeoma', 'Halima', 'Chukwudi', 'Khadija', 'Obinna', 'Hauwa',
            'Ikenna', 'Safiya', 'Chidi', 'Maryam', 'Uchenna', 'Bilkisu', 'Nnamdi', 'Hadiza',
            'Chibueze', 'Asma', 'Kelechi', 'Jamila', 'Chukwuemeka', 'Rukayya', 'Ifeanyi', 'Asmau'
        ];

        // Nigerian last names
        $lastNames = [
            'Okafor', 'Ahmed', 'Eze', 'Bello', 'Nwankwo', 'Yusuf', 'Okonkwo', 'Ibrahim',
            'Okoro', 'Musa', 'Ojo', 'Usman', 'Adeyemi', 'Suleiman', 'Chukwu', 'Aliyu',
            'Obi', 'Abubakar', 'Ike', 'Garba', 'Nwosu', 'Lawal', 'Onyeka', 'Sani',
            'Udeh', 'Danjuma', 'Okeke', 'Bala', 'Nnaji', 'Kabir'
        ];

        $genders = ['Male', 'Female'];
        $studentCount = Student::count();

        // Create 30 students per class (5 per section)
        foreach ($classes as $class) {
            $sections = Section::where('class_id', $class->id)->get();
            
            foreach ($sections as $section) {
                for ($i = 0; $i < 10; $i++) {
                    $studentCount++;
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $gender = $genders[array_rand($genders)];
                    
                    Student::create([
                        'admission_number' => 'ADM-2026-' . str_pad($studentCount, 4, '0', STR_PAD_LEFT),
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'class_id' => $class->id,
                        'section_id' => $section->id,
                        'gender' => $gender,
                        'guardian_name' => $lastName . ' ' . $firstNames[array_rand($firstNames)],
                        'guardian_phone' => '+234 ' . rand(700, 909) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                        'status' => 'Active',
                    ]);
                }
            }
        }

        $this->command->info("Created {$studentCount} students across all classes!");
        $this->command->info("Created " . count($teachers) . " teachers!");
    }
}
