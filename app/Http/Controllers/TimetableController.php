<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\TimetableEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function downloadPdf(Request $request)
    {
        $data = $this->buildTimetableData($request);

        $pdf = Pdf::loadView('pdf.timetable', $data)->setPaper('a4', 'landscape');

        $filename = 'Timetable_' . str_replace(' ', '_', $data['class']->name);
        if ($data['section']) {
            $filename .= '_' . str_replace(' ', '_', $data['section']->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function printView(Request $request)
    {
        $data = $this->buildTimetableData($request);
        return view('timetable.print', $data);
    }

    private function buildTimetableData(Request $request): array
    {
        $classId = (int) $request->query('class_id');
        $sectionId = $request->query('section_id') ? (int) $request->query('section_id') : null;

        $user = auth()->user();
        if ($user?->role === 'parent') {
            $allowedClassIds = $user->students()->pluck('class_id')->toArray();
            abort_unless(in_array($classId, $allowedClassIds), 403, 'Unauthorized access to class timetable.');
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

        $hasSaturday = $entries->where('day_of_week', 6)->isNotEmpty();
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
        ];
        if ($hasSaturday) {
            $days[6] = 'Saturday';
        }

        // Build time-slot grid
        $boundaries = [];
        if ($entries->isEmpty()) {
            for ($hour = 8; $hour <= 14; $hour++) {
                $boundaries[] = sprintf('%02d:00', $hour);
            }
        } else {
            foreach ($entries as $entry) {
                $boundaries[] = substr((string) $entry->starts_at, 0, 5);
                $boundaries[] = substr((string) $entry->ends_at, 0, 5);
            }
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

        // School info
        $schoolName = config('academyhub.school_name', config('app.name', 'School'));
        $schoolAddress = config('academyhub.school_address', '');
        $schoolPhone = config('academyhub.school_phone', '');
        $schoolEmail = config('academyhub.school_email', '');
        $schoolTagline = config('academyhub.tagline', '');
        $logoPath = config('academyhub.school_logo');

        $logoBase64 = null;
        if ($logoPath) {
            $fullPath = storage_path('app/public/' . $logoPath);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        // Term + session
        $activeTerm = AcademicTerm::active();
        $termLabel = $activeTerm?->name ?? 'Term';
        $sessionLabel = $activeTerm?->session?->name ?? AcademicSession::activeName() ?? now()->format('Y');

        return [
            'class' => $class,
            'section' => $section,
            'days' => $days,
            'timeSlots' => $timeSlots,
            'slotMap' => $slotMap,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
            'schoolTagline' => $schoolTagline,
            'logoBase64' => $logoBase64,
            'termLabel' => $termLabel,
            'sessionLabel' => $sessionLabel,
        ];
    }

    private function timeToSeconds(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time, 2));
        return ($h * 3600) + ($m * 60);
    }
}
