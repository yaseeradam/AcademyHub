<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TimetableEntry;
use Illuminate\Database\Seeder;

class NurseryTimetableSeeder extends Seeder
{
    public function run(): void
    {
        // Find or create Nursery class
        $nurseryClass = SchoolClass::query()->where('name', 'like', '%Nursery%')->first()
            ?? SchoolClass::query()->create(['name' => 'Nursery Class', 'level' => 1]);

        // Define subjects from the reference timetable
        $subjectsData = [
            'Quran' => 'blue',
            'Math' => 'green',
            'English' => 'amber',
            'Phonics' => 'purple',
            'Spelling' => 'pink',
            'Science' => 'emerald',
            'ICT' => 'teal',
            'Story' => 'sky',
            'Quiz' => 'rose',
        ];

        $subjectModels = [];
        $tenantId = $nurseryClass->tenant_id ?? 1;
        foreach ($subjectsData as $name => $color) {
            $code = strtoupper(substr($name, 0, 3)) . rand(100, 999);
            $subjectModels[$name] = Subject::query()->firstOrCreate(
                ['name' => $name],
                ['code' => $code, 'tenant_id' => $tenantId]
            );
        }

        // Clear existing entries for this class
        TimetableEntry::query()->where('class_id', $nurseryClass->id)->delete();

        // Schedule mapping by day
        $schedule = [
            1 => [ // Monday
                ['08:00', '08:30', 'Quran', false, null, 'blue'],
                ['08:30', '09:30', 'Math', false, null, 'blue'],
                ['09:30', '10:30', 'English', false, null, 'blue'],
                ['10:30', '11:00', null, true, 'Breakfast', 'amber'],
                ['11:00', '11:50', 'Phonics', false, null, 'blue'],
                ['11:50', '12:40', 'Spelling', false, null, 'blue'],
                ['12:40', '13:10', 'Spelling', false, null, 'blue'],
            ],
            2 => [ // Tuesday
                ['08:00', '08:30', 'Quran', false, null, 'green'],
                ['08:30', '09:30', 'Phonics', false, null, 'green'],
                ['09:30', '10:30', 'Math', false, null, 'green'],
                ['10:30', '11:00', null, true, 'Breakfast', 'amber'],
                ['11:00', '11:50', 'English', false, null, 'green'],
                ['11:50', '12:40', 'ICT', false, null, 'green'],
                ['12:40', '13:10', 'ICT', false, null, 'green'],
            ],
            3 => [ // Wednesday
                ['08:00', '08:30', 'English', false, null, 'amber'],
                ['08:30', '09:30', 'Math', false, null, 'amber'],
                ['09:30', '10:30', 'Quran', false, null, 'amber'],
                ['10:30', '11:00', null, true, 'Breakfast', 'amber'],
                ['11:00', '11:50', 'Phonics', false, null, 'amber'],
                ['11:50', '12:40', 'Spelling', false, null, 'amber'],
                ['12:40', '13:10', 'Spelling', false, null, 'amber'],
            ],
            4 => [ // Thursday
                ['08:00', '08:30', 'Math', false, null, 'purple'],
                ['08:30', '09:30', 'Science', false, null, 'purple'],
                ['09:30', '10:30', 'English', false, null, 'purple'],
                ['10:30', '11:00', null, true, 'Breakfast', 'amber'],
                ['11:00', '11:50', 'ICT', false, null, 'purple'],
                ['11:50', '12:40', 'Story', false, null, 'purple'],
                ['12:40', '13:10', 'Story', false, null, 'purple'],
            ],
            5 => [ // Friday
                ['08:00', '08:30', 'Science', false, null, 'pink'],
                ['08:30', '09:30', 'English', false, null, 'pink'],
                ['09:30', '10:30', 'Phonics', false, null, 'pink'],
                ['10:30', '11:00', null, true, 'Breakfast', 'amber'],
                ['11:00', '11:50', 'Quran', false, null, 'pink'],
                ['11:50', '12:40', 'Quiz', false, null, 'pink'],
                ['12:40', '13:10', 'Quiz', false, null, 'pink'],
            ],
        ];

        foreach ($schedule as $day => $slots) {
            foreach ($slots as [$start, $end, $subName, $isBreak, $breakText, $color]) {
                TimetableEntry::query()->create([
                    'class_id' => $nurseryClass->id,
                    'day_of_week' => $day,
                    'starts_at' => $start . ':00',
                    'ends_at' => $end . ':00',
                    'is_break' => $isBreak,
                    'break_text' => $breakText,
                    'subject_id' => $subName ? $subjectModels[$subName]->id : null,
                    'color' => $color,
                ]);
            }
        }
    }
}
