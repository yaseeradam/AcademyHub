<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('MYACADEMY_ADMIN_EMAIL', 'admin@myacademy.local')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('MYACADEMY_ADMIN_PASSWORD', 'password')),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'bursar@myacademy.local'],
            [
                'name' => 'Bursar',
                'password' => Hash::make('password'),
                'role' => 'bursar',
                'is_active' => true,
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'teacher@myacademy.local'],
            [
                'name' => 'Teacher',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'is_active' => true,
            ]
        );

        $classNames = [
            ['name' => 'JSS 1', 'level' => 1],
            ['name' => 'JSS 2', 'level' => 2],
            ['name' => 'JSS 3', 'level' => 3],
            ['name' => 'SSS 1', 'level' => 4],
            ['name' => 'SSS 2', 'level' => 5],
            ['name' => 'SSS 3', 'level' => 6],
        ];

        foreach ($classNames as $row) {
            $class = SchoolClass::query()->firstOrCreate(['name' => $row['name']], $row);

            foreach (['A', 'B', 'C'] as $sectionName) {
                Section::query()->firstOrCreate([
                    'class_id' => $class->id,
                    'name' => $sectionName,
                ]);
            }
        }

        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MTH'],
            ['name' => 'English Language', 'code' => 'ENG'],
            ['name' => 'Basic Science', 'code' => 'BSC'],
            ['name' => 'Social Studies', 'code' => 'SOS'],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->firstOrCreate(['code' => $subject['code']], $subject);
        }

        $teacher = User::query()->where('email', 'teacher@myacademy.local')->first();
        $jss2 = SchoolClass::query()->where('name', 'JSS 2')->first();

        if ($teacher && $jss2) {
            $subjectIds = Subject::query()->pluck('id');

            foreach ($subjectIds as $subjectId) {
                SubjectAllocation::query()->firstOrCreate([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subjectId,
                    'class_id' => $jss2->id,
                ]);
            }
        }

        $defaultTuitionByClassLevel = [
            1 => 40000,
            2 => 45000,
            3 => 45000,
            4 => 55000,
            5 => 55000,
            6 => 60000,
        ];

        foreach (SchoolClass::query()->get() as $class) {
            $amount = $defaultTuitionByClassLevel[$class->level] ?? 50000;

            FeeStructure::query()->firstOrCreate(
                [
                    'class_id' => $class->id,
                    'category' => 'Tuition',
                    'term' => null,
                    'session' => null,
                ],
                [
                    'amount_due' => $amount,
                ]
            );
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;
        $defaultSession = "{$year}/{$next}";

        if (! AcademicSession::query()->where('is_active', true)->exists()) {
            AcademicSession::query()->firstOrCreate(
                ['name' => $defaultSession],
                ['is_active' => true]
            );
        } else {
            AcademicSession::query()->firstOrCreate(['name' => $defaultSession]);
        }

        if (Student::query()->count() === 0) {
            $jss2a = $jss2 ? Section::query()->where('class_id', $jss2->id)->where('name', 'A')->first() : null;

            if ($jss2 && $jss2a) {
                Student::query()->create([
                    'admission_number' => 'ADM-2026-0092',
                    'first_name' => 'Amina',
                    'last_name' => 'Yusuf',
                    'class_id' => $jss2->id,
                    'section_id' => $jss2a->id,
                    'gender' => 'Female',
                    'guardian_name' => 'Yusuf Ibrahim',
                    'guardian_phone' => '+234 801 234 5678',
                    'status' => 'Active',
                ]);
            }
        }

        // Seed parent accounts if they don't exist
        if (User::query()->where('role', 'parent')->count() === 0) {
            $this->call(ParentSeeder::class);
        }
    }
}
