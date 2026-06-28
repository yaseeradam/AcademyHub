<?php

namespace App\Livewire\Timetable;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Timetable')]
class Index extends Component
{
    public $classId = null;
    public $sectionId = null;
    public ?int $day = null;
    public $debugPing = null;
    
    public string $viewMode = 'grid'; // 'grid' or 'daily'
    public int $activeDayTab = 1;

    public ?int $editingId = null;
    public ?int $entryClassId = null;
    public ?int $entrySectionId = null;
    public int $entryDay = 1;
    public string $startsAt = '08:00';
    public string $endsAt = '09:00';
    public ?int $subjectId = null;
    public ?int $teacherId = null;
    public ?string $room = null;
    public bool $isBreak = false;
    public ?string $breakText = null;
    public string $color = 'slate';
    public bool $applyToAllDays = false;

    #[Computed]
    public function classes()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->role === 'admin') {
            return SchoolClass::query()->orderBy('level')->get();
        }

        $ids = SubjectAllocation::query()->where('teacher_id', $user->id)->pluck('class_id')->unique();

        return SchoolClass::query()
            ->whereIn('id', $ids)
            ->orderBy('level')
            ->get();
    }

    #[Computed]
    public function sections()
    {
        if (! $this->classId) {
            return collect();
        }

        return Section::query()->where('class_id', $this->classId)->orderBy('name')->get();
    }

    #[Computed]
    public function entrySections()
    {
        if (! $this->entryClassId) {
            return collect();
        }

        return Section::query()->where('class_id', $this->entryClassId)->orderBy('name')->get();
    }

    #[Computed]
    public function subjects()
    {
        return Subject::query()->orderBy('name')->get();
    }

    #[Computed]
    public function teachers()
    {
        return User::query()
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $day = (int) now()->isoWeekday();
        if ($day > 6) {
            $day = 1;
        }
        $this->activeDayTab = $day;
        $this->entryDay = $day;
    }

    public function updatedClassId(): void
    {
        \Log::info('Timetable: updatedClassId fired', ['classId' => $this->classId, 'type' => gettype($this->classId)]);
        $this->sectionId = null;
        $this->editingId = null;
        unset($this->sections);
    }

    public function updatedEntryClassId(): void
    {
        $this->entrySectionId = null;
        unset($this->entrySections);
    }

    public function clearForm(): void
    {
        $this->editingId = null;
        $this->entryClassId = $this->classId;
        $this->entrySectionId = null;
        $day = (int) now()->isoWeekday();
        $this->entryDay = $day > 5 ? 1 : $day;
        $this->startsAt = '08:00';
        $this->endsAt = '09:00';
        $this->subjectId = null;
        $this->teacherId = null;
        $this->room = null;
        $this->isBreak = false;
        $this->breakText = null;
        $this->color = 'slate';
        $this->applyToAllDays = false;
    }

    public function selectSlot(int $day, string $start, string $end): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $this->clearForm();
        $this->entryDay = $day;
        $this->startsAt = $start;
        $this->endsAt = $end;
    }

    public function edit(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $e = TimetableEntry::query()->findOrFail($id);

        $this->editingId = $e->id;
        $this->entryDay = (int) $e->day_of_week;
        $this->startsAt = substr((string) $e->starts_at, 0, 5);
        $this->endsAt = substr((string) $e->ends_at, 0, 5);
        $this->subjectId = $e->subject_id;
        $this->teacherId = $e->teacher_id;
        $this->room = $e->room;
        $this->isBreak = (bool) $e->is_break;
        $this->breakText = $e->break_text;
        $this->color = $e->color ?? 'slate';
        $this->applyToAllDays = false;
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $rules = [
            'entryDay' => ['required', 'integer', 'between:1,6'],
            'startsAt' => ['required', 'date_format:H:i'],
            'endsAt' => ['required', 'date_format:H:i'],
            'isBreak' => ['required', 'boolean'],
            'breakText' => ['nullable', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:30'],
        ];

        if ($this->isBreak) {
            $rules['subjectId'] = ['nullable'];
            $rules['teacherId'] = ['nullable'];
            $rules['room'] = ['nullable'];
        } else {
            $rules['subjectId'] = ['required', 'integer', 'exists:subjects,id'];
            $rules['teacherId'] = ['nullable', 'integer', 'exists:users,id'];
            $rules['room'] = ['nullable', 'string', 'max:50'];
        }

        $data = $this->validate($rules);

        abort_unless($this->classId, 400);

        $startSec = $this->timeToSeconds($data['startsAt']);
        $endSec = $this->timeToSeconds($data['endsAt']);

        if ($endSec <= $startSec) {
            throw ValidationException::withMessages([
                'endsAt' => 'End time must be after start time.',
            ]);
        }

        $daysToSave = [$data['entryDay']];
        if ($this->applyToAllDays && ! $this->editingId) {
            $daysToSave = [1, 2, 3, 4, 5, 6];
        }

        // 1. Conflict Check
        foreach ($daysToSave as $day) {
            $this->ensureNoConflicts(
                editingId: $this->editingId,
                classId: (int) $this->classId,
                sectionId: null,
                day: $day,
                startSec: $startSec,
                endSec: $endSec,
                teacherId: (!$this->isBreak && $data['teacherId']) ? (int) $data['teacherId'] : null
            );
        }

        // 2. Save
        foreach ($daysToSave as $day) {
            TimetableEntry::query()->updateOrCreate(
                $this->editingId ? ['id' => $this->editingId] : [
                    'class_id' => (int) $this->classId,
                    'day_of_week' => $day,
                    'starts_at' => $data['startsAt'],
                    'ends_at' => $data['endsAt'],
                ],
                [
                    'class_id' => (int) $this->classId,
                    'section_id' => null,
                    'day_of_week' => $day,
                    'starts_at' => $data['startsAt'],
                    'ends_at' => $data['endsAt'],
                    'subject_id' => !$this->isBreak ? (int) $data['subjectId'] : null,
                    'teacher_id' => (!$this->isBreak && $data['teacherId']) ? (int) $data['teacherId'] : null,
                    'room' => (!$this->isBreak && $data['room']) ? trim($data['room']) : null,
                    'is_break' => (bool) $this->isBreak,
                    'break_text' => $this->isBreak ? ($data['breakText'] ? trim($data['breakText']) : 'BREAK') : null,
                    'color' => $data['color'],
                ]
            );
        }

        $this->dispatch('alert', message: 'Timetable entry saved.', type: 'success');
        $this->clearForm();
    }

    public function delete(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        TimetableEntry::query()->whereKey($id)->delete();

        if ($this->editingId === $id) {
            $this->clearForm();
        }

        $this->dispatch('alert', message: 'Entry deleted.', type: 'success');
    }

    private function ensureNoConflicts(
        ?int $editingId,
        int $classId,
        ?int $sectionId,
        int $day,
        int $startSec,
        int $endSec,
        ?int $teacherId
    ): void {
        $existing = TimetableEntry::query()
            ->where('class_id', $classId)
            ->where('day_of_week', $day)
            ->get();

        foreach ($existing as $ex) {
            if ($editingId && (int) $ex->id === (int) $editingId) {
                continue;
            }

            if ($sectionId === null) {
                // class-wide entry cannot overlap with any section entry
            } else {
                // section entry overlaps with same section or class-wide entries
                if ($ex->section_id !== null && (int) $ex->section_id !== (int) $sectionId) {
                    continue;
                }
            }

            $exStartSec = $this->timeToSeconds(substr((string) $ex->starts_at, 0, 5));
            $exEndSec = $this->timeToSeconds(substr((string) $ex->ends_at, 0, 5));

            if ($this->overlaps($startSec, $endSec, $exStartSec, $exEndSec)) {
                throw ValidationException::withMessages([
                    'startsAt' => 'Time overlaps an existing class timetable entry.',
                ]);
            }
        }

        if ($teacherId) {
            $teacherEntries = TimetableEntry::query()
                ->where('teacher_id', $teacherId)
                ->where('day_of_week', $day)
                ->get();

            foreach ($teacherEntries as $ex) {
                if ($editingId && (int) $ex->id === (int) $editingId) {
                    continue;
                }

                $exStartSec = $this->timeToSeconds(substr((string) $ex->starts_at, 0, 5));
                $exEndSec = $this->timeToSeconds(substr((string) $ex->ends_at, 0, 5));

                if ($this->overlaps($startSec, $endSec, $exStartSec, $exEndSec)) {
                    throw ValidationException::withMessages([
                        'teacherId' => 'Teacher already has a timetable entry at this time.',
                    ]);
                }
            }
        }
    }

    private function overlaps(int $aStart, int $aEnd, int $bStart, int $bEnd): bool
    {
        return max($aStart, $bStart) < min($aEnd, $bEnd);
    }

    private function timeToSeconds(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time, 2));
        return ($h * 3600) + ($m * 60);
    }

    private function dayLabel(int $day): string
    {
        return match ($day) {
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            default => 'Day',
        };
    }

    private function buildTimeSlots($entries): array
    {
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
            $unique[$this->timeToSeconds($time)] = $time;
        }

        ksort($unique);
        $sortedTimes = array_values($unique);

        $slots = [];
        for ($i = 0; $i < count($sortedTimes) - 1; $i++) {
            $start = $sortedTimes[$i];
            $end = $sortedTimes[$i + 1];
            $slots[] = [
                'key' => $start.'-'.$end,
                'start' => $start,
                'end' => $end,
                'label' => $start.' - '.$end,
                'startSec' => $this->timeToSeconds($start),
                'endSec' => $this->timeToSeconds($end),
            ];
        }

        return $slots;
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (! in_array($user->role, ['admin', 'teacher', 'bursar'], true)) {
            abort(403);
        }

        // Ensure classId is properly cast
        $classId = $this->classId ? (int) $this->classId : null;

        $entries = $classId
            ? TimetableEntry::query()
                ->with(['subject:id,name', 'teacher:id,name'])
                ->where('class_id', $classId)
                ->orderBy('day_of_week')
                ->orderBy('starts_at')
                ->get()
            : collect();

        $days = collect([1, 2, 3, 4, 5, 6])->map(fn ($day) => [
            'day' => $day,
            'label' => $this->dayLabel($day),
        ])->all();

        $timeSlots = $this->buildTimeSlots($entries);

        $slotMap = [];
        foreach ($entries as $entry) {
            $entryStart = $this->timeToSeconds(substr((string) $entry->starts_at, 0, 5));
            $entryEnd = $this->timeToSeconds(substr((string) $entry->ends_at, 0, 5));

            foreach ($timeSlots as $slot) {
                if ($this->overlaps($entryStart, $entryEnd, $slot['startSec'], $slot['endSec'])) {
                    $slotMap[$entry->day_of_week][$slot['key']] = $entry;
                }
            }
        }

        return view('livewire.timetable.index', [
            'isAdmin' => $user->role === 'admin',
            'days' => $days,
            'timeSlots' => $timeSlots,
            'slotMap' => $slotMap,
            'entries' => $entries,
        ]);
    }
}
