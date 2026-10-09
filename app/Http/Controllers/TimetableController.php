<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SubjectAllocation;
use App\Models\TimetableEntry;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function downloadPdf(Request $request)
    {
        $classId = (int) $request->query('class_id');
        $sectionId = $request->query('section_id') ? (int) $request->query('section_id') : null;

        $user = auth()->user();
        if ($user?->role === 'parent') {
            $allowedClassIds = $user->students()->pluck('class_id')->toArray();
            abort_unless(in_array($classId, $allowedClassIds), 403, 'Unauthorized access to class timetable.');
        } elseif ($user?->role === 'teacher') {
            $hasAllocation = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $classId)
                ->exists();
            abort_unless($hasAllocation, 403, 'Unauthorized access to class timetable.');
        }

        $class = SchoolClass::query()->findOrFail($classId);
        $section = $sectionId ? Section::query()->findOrFail($sectionId) : null;

        $entries = TimetableEntry::query()
            ->with(['subject:id,name', 'teacher:id,name'])
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();

        // Build days
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
        ];

        if ($entries->contains(fn($e) => (int) $e->day_of_week === 6)) {
            $days[6] = 'Saturday';
        }

        // Build time-slot grid (same logic as the Livewire editor)
        $boundaries = [];
        for ($hour = 8; $hour <= 16; $hour++) {
            $boundaries[] = sprintf('%02d:00', $hour);
        }
        foreach ($entries as $entry) {
            $boundaries[] = substr((string) $entry->starts_at, 0, 5);
            $boundaries[] = substr((string) $entry->ends_at, 0, 5);
        }

        $unique = [];
        foreach ($boundaries as $time) {
            [$h, $m] = array_map('intval', explode(':', $time, 2));
            $sec = ($h * 3600) + ($m * 60);
            $unique[$sec] = $time;
        }
        ksort($unique);
        $sortedTimes = array_values($unique);

        $timeSlots = [];
        for ($i = 0; $i < count($sortedTimes) - 1; $i++) {
            $start = $sortedTimes[$i];
            $end = $sortedTimes[$i + 1];
            $timeSlots[] = [
                'key' => $start . '-' . $end,
                'start' => $start,
                'end' => $end,
                'label' => $start . ' – ' . $end,
                'startSec' => $this->timeToSeconds($start),
                'endSec' => $this->timeToSeconds($end),
            ];
        }

        // Map entries into the grid (day × slot)
        $slotMap = [];
        foreach ($entries as $entry) {
            $entryStart = $this->timeToSeconds(substr((string) $entry->starts_at, 0, 5));
            $entryEnd = $this->timeToSeconds(substr((string) $entry->ends_at, 0, 5));

            foreach ($timeSlots as $slot) {
                if (max($entryStart, $slot['startSec']) < min($entryEnd, $slot['endSec'])) {
                    $slotMap[$entry->day_of_week][$slot['key']] = $entry;
                }
            }
        }

        // Conventional slots & mapping matching the poster
        $conventionalSlots = [
            ['period' => 1, 'label' => '1', 'time' => '8:00am – 8:30am', 'start' => '08:00', 'end' => '08:30'],
            ['period' => 2, 'label' => '2', 'time' => '8:30am – 9:00am', 'start' => '08:30', 'end' => '09:00'],
            ['period' => 3, 'label' => '3', 'time' => '9:00am – 9:30am', 'start' => '09:00', 'end' => '09:30'],
            ['period' => 'break', 'label' => 'BREAK', 'time' => '9:30am – 9:40am', 'start' => '09:30', 'end' => '09:40'],
            ['period' => 4, 'label' => '4', 'time' => '9:40am – 10:10am', 'start' => '09:40', 'end' => '10:10'],
            ['period' => 5, 'label' => '5', 'time' => '10:10am – 10:40am', 'start' => '10:10', 'end' => '10:40'],
            ['period' => 6, 'label' => '6', 'time' => '10:40am – 1:10pm', 'start' => '10:40', 'end' => '13:10'],
        ];

        $conventionalMap = [];
        foreach ($entries as $entry) {
            $eStart = $this->timeToSeconds(substr((string) $entry->starts_at, 0, 5));
            $eEnd = $this->timeToSeconds(substr((string) $entry->ends_at, 0, 5));

            foreach ($conventionalSlots as $idx => $cSlot) {
                $cStart = $this->timeToSeconds($cSlot['start']);
                $cEnd = $this->timeToSeconds($cSlot['end']);

                if (max($eStart, $cStart) < min($eEnd, $cEnd)) {
                    $conventionalMap[$entry->day_of_week][$idx] = $entry;
                }
            }
        }

        // Class teacher lookup
        $classTeacherName = SubjectAllocation::query()
            ->where('class_id', $classId)
            ->whereHas('teacher', fn($q) => $q->where('is_class_teacher', true))
            ->with('teacher:id,name')
            ->first()?->teacher?->name
            ?? SubjectAllocation::query()
                ->where('class_id', $classId)
                ->whereNotNull('teacher_id')
                ->with('teacher:id,name')
                ->first()?->teacher?->name
            ?? $entries->firstWhere('teacher_id', '!=', null)?->teacher?->name;

        // School info
        $schoolName = config('academyhub.school_name', config('app.name', 'AI INTEGRATED ACADEMY ARGUNGU'));
        $schoolMotto = config('academyhub.school_motto', 'LEARNING TODAY LEADING TOMORROW');
        $schoolAddress = config('academyhub.school_address', '');
        $schoolPhone = config('academyhub.school_phone', '');
        $schoolEmail = config('academyhub.school_email', '');
        $logoPath = config('academyhub.school_logo');

        $logoBase64 = null;
        if ($logoPath) {
            $candidates = [
                storage_path('app/public/' . $logoPath),
                public_path('uploads/' . $logoPath),
                public_path($logoPath),
            ];
            foreach ($candidates as $fullPath) {
                if (file_exists($fullPath) && !is_dir($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                    break;
                }
            }
        }

        // Term + session
        $activeTerm = AcademicTerm::active();
        $termLabel = $activeTerm?->name ?? 'First Term';
        $sessionLabel = $activeTerm?->session?->name ?? AcademicSession::activeName() ?? now()->format('Y') . '/' . (now()->year + 1);

        $pdf = Pdf::loadView('pdf.timetable', [
            'class' => $class,
            'section' => $section,
            'days' => $days,
            'timeSlots' => $timeSlots,
            'slotMap' => $slotMap,
            'conventionalSlots' => $conventionalSlots,
            'conventionalMap' => $conventionalMap,
            'classTeacherName' => $classTeacherName,
            'schoolName' => $schoolName,
            'schoolMotto' => $schoolMotto,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
            'logoBase64' => $logoBase64,
            'termLabel' => $termLabel,
            'sessionLabel' => $sessionLabel,
        ])->setPaper('a4', 'landscape');

        $filename = 'Timetable_' . str_replace(' ', '_', $class->name);
        if ($section) {
            $filename .= '_' . str_replace(' ', '_', $section->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function timeToSeconds(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time, 2));
        return ($h * 3600) + ($m * 60);
    }
}
