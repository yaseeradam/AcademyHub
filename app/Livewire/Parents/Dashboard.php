<?php

namespace App\Livewire\Parents;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceExcuse;
use App\Models\AttendanceMark;
use App\Models\Homework;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Parent Dashboard')]
class Dashboard extends Component
{
    public ?int $selectedChildId = null;
    public int $term = 1;
    public string $session = '';
    public string $activeTab = 'overview';
    public ?int $selectedTopicSubjectId = null;

    // Attendance Tracker State
    public string $attendanceMonth = '';
    public bool $showAbsenceModal = false;
    public string $absenceStartDate = '';
    public string $absenceEndDate = '';
    public string $absenceReason = 'Illness / Medical';
    public string $absenceNotes = '';

    public function mount(): void
    {
        $tenant = auth()->user()?->tenant;
        if ($tenant && $tenant->isSubscriptionExpired()) {
            abort(403, 'Your school\'s subscription has expired. Access to Parent Portal is disabled.');
        }

        $this->term            = AcademicTerm::activeTermNumber();
        $this->session         = AcademicSession::activeName() ?? $this->defaultSession();
        $this->attendanceMonth = now()->format('Y-m');
        $this->absenceStartDate = now()->toDateString();
        $this->absenceEndDate   = now()->toDateString();

        $children = $this->children;
        if ($children->count() === 1) {
            $this->selectedChildId = $children->first()->id;
        }
    }

    #[Computed]
    public function children(): Collection
    {
        $tenant = auth()->user()?->tenant;
        if (!$tenant) {
            return collect();
        }

        $dbComponent = $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->first();
        if (!$dbComponent) {
            return collect();
        }

        $allowedClassIds = $dbComponent->pivot->allowed_class_ids ?? [];
        if (is_string($allowedClassIds)) {
            $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
        }
        $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];

        return auth()->user()->students()
            ->whereIn('class_id', $allowedClassIds)
            ->with(['schoolClass', 'section', 'user', 'scores'])
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function selectedChild(): ?Student
    {
        if (! $this->selectedChildId) return null;
        return $this->children->firstWhere('id', $this->selectedChildId);
    }

    #[Computed]
    public function resultsPublished(): bool
    {
        if (! $this->selectedChild) return false;
        return ResultPublication::where('class_id', $this->selectedChild->class_id)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->whereNotNull('published_at')
            ->exists();
    }

    #[Computed]
    public function scores(): Collection
    {
        if (! $this->selectedChild || ! $this->resultsPublished) return collect();
        return Score::where('student_id', $this->selectedChild->id)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();
    }

    #[Computed]
    public function performanceStats(): array
    {
        $scores = $this->scores;
        if ($scores->isEmpty()) return ['average' => 0, 'subjects' => 0, 'passed' => 0, 'failed' => 0, 'position' => null, 'classSize' => 0];

        $maxTotal = max(1,
            (int) config('academyhub.results_ca1_max', 20) +
            (int) config('academyhub.results_ca2_max', 20) +
            (int) config('academyhub.results_exam_max', 60)
        );

        $average   = round($scores->avg('total'), 1);
        $passed    = $scores->whereNotIn('grade', ['F', 'E'])->count();
        $failed    = $scores->whereIn('grade', ['F', 'E'])->count();
        $myTotal   = $scores->sum('total');

        $classSize = Student::where('class_id', $this->selectedChild->class_id)->where('status', 'Active')->count();
        $higher    = Score::where('class_id', $this->selectedChild->class_id)
            ->where('session', $this->session)->where('term', $this->term)
            ->selectRaw('student_id, SUM(total) as grand_total')
            ->groupBy('student_id')
            ->havingRaw('SUM(total) > ?', [$myTotal])
            ->count();
        $position = $higher + 1;

        return compact('average', 'passed', 'failed', 'position', 'classSize', 'maxTotal') + ['subjects' => $scores->count()];
    }

    #[Computed]
    public function attendance(): array
    {
        if (! $this->selectedChild) {
            return ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0, 'rate' => 0];
        }

        $marks = AttendanceMark::where('student_id', $this->selectedChild->id)
            ->whereHas('sheet', fn ($q) => $q->where('term', $this->term)->where('session', $this->session))
            ->get();

        $present = $marks->where('status', 'Present')->count();
        $absent  = $marks->where('status', 'Absent')->count();
        $late    = $marks->where('status', 'Late')->count();
        $excused = $marks->where('status', 'Excused')->count();
        $total   = $marks->count();

        // Effective total for percentage: exclude approved excused leaves so student isn't penalized
        $effectiveTotal = max(1, $total - $excused);
        $rate = $total > 0 ? round((($present + $late) / $effectiveTotal) * 100, 1) : 0;
        $rate = min(100, $rate);

        return compact('present', 'absent', 'late', 'excused', 'total', 'rate');
    }

    #[Computed]
    public function fees(): array
    {
        if (! $this->selectedChild) return ['paid' => 0, 'outstanding' => 0, 'total' => 0];

        $ledger = \App\Support\BillingService::getStudentTermLedger(
            $this->selectedChild,
            $this->session,
            $this->term,
            'Tuition'
        );

        return [
            'paid'         => $ledger['total_paid'],
            'outstanding'  => $ledger['total_balance'],
            'total'        => $ledger['total_due'],
            'current_term' => $ledger['current_term_due'],
            'arrears'      => $ledger['past_arrears'],
        ];
    }

    #[Computed]
    public function homework(): Collection
    {
        if (! $this->selectedChild) return collect();

        return Homework::where('class_id', $this->selectedChild->class_id)
            ->where(fn ($q) => $q->whereNull('section_id')->orWhere('section_id', $this->selectedChild->section_id))
            ->with(['subject', 'submissions' => fn ($q) => $q->where('student_id', $this->selectedChild->id)])
            ->orderByDesc('due_date')
            ->limit(6)
            ->get();
    }

    #[Computed]
    public function recentAttendance(): Collection
    {
        if (! $this->selectedChild) return collect();

        return AttendanceMark::where('student_id', $this->selectedChild->id)
            ->with('sheet')
            ->whereHas('sheet')
            ->get()
            ->sortByDesc(fn ($m) => $m->sheet->date)
            ->take(7);
    }

    #[Computed]
    public function todayAttendance(): array
    {
        if (! $this->selectedChild) {
            return [
                'has_record'   => false,
                'status'       => 'No Child Selected',
                'badge_class'  => 'bg-slate-100 text-slate-600',
                'time_in'      => null,
                'time_out'     => null,
                'source'       => null,
                'is_biometric' => false,
                'note'         => null,
                'message'      => 'Select a child to view today\'s attendance status.'
            ];
        }

        $today = now()->toDateString();
        $mark = AttendanceMark::where('student_id', $this->selectedChild->id)
            ->whereHas('sheet', fn ($q) => $q->whereDate('date', $today))
            ->with('sheet.takenBy')
            ->first();

        if (! $mark) {
            $excuse = AttendanceExcuse::activeForStudent($this->selectedChild->id, $today)->first();
            if ($excuse) {
                return [
                    'has_record'   => true,
                    'status'       => 'Excused',
                    'badge_class'  => 'bg-purple-50 text-purple-700 border border-purple-200/80',
                    'time_in'      => null,
                    'time_out'     => null,
                    'source'       => 'Parent Notice',
                    'is_biometric' => false,
                    'note'         => $excuse->reason . ($excuse->notes ? ": {$excuse->notes}" : ''),
                    'message'      => "Excused absence recorded for today ({$excuse->reason})."
                ];
            }

            $hour = (int) now()->format('H');
            $isMorning = $hour < 9;
            return [
                'has_record'   => false,
                'status'       => $isMorning ? 'Awaiting Clock-In' : 'Unmarked Today',
                'badge_class'  => $isMorning ? 'bg-amber-50 text-amber-700 border border-amber-200/80' : 'bg-slate-100 text-slate-600 border border-slate-200',
                'time_in'      => null,
                'time_out'     => null,
                'source'       => null,
                'is_biometric' => false,
                'note'         => null,
                'message'      => $isMorning ? 'Student has not clocked in yet this morning.' : 'No biometric or roll call mark recorded for today.'
            ];
        }

        $timeIn = $mark->arrived_at ? Carbon::parse($mark->arrived_at)->format('h:i A') : null;
        $timeOut = $mark->departed_at ? Carbon::parse($mark->departed_at)->format('h:i A') : null;

        if (! $timeIn && preg_match('/In:\s*([0-9:AMP\s]+)/i', $mark->note ?? '', $m)) {
            $timeIn = trim($m[1]);
        }
        if (! $timeOut && preg_match('/Out:\s*([0-9:AMP\s]+)/i', $mark->note ?? '', $m)) {
            $timeOut = trim($m[1]);
        }

        $isBiometric = false;
        $noteLower = strtolower($mark->note ?? '');
        if (str_contains($noteLower, 'k40') || str_contains($noteLower, 'biometric') || str_contains($noteLower, 'zk')) {
            $isBiometric = true;
        }

        $source = $isBiometric ? 'K40 Biometrics' : ($mark->sheet?->takenBy?->name ?? 'Teacher Roll Call');

        $status = $mark->status;
        $badgeClass = match ($status) {
            'Present' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
            'Late'    => 'bg-amber-50 text-amber-700 border border-amber-200/80',
            'Excused' => 'bg-purple-50 text-purple-700 border border-purple-200/80',
            'Absent'  => 'bg-rose-50 text-rose-700 border border-rose-200/80',
            default   => 'bg-slate-100 text-slate-700 border border-slate-200',
        };

        $message = match ($status) {
            'Present' => $timeOut ? "Clocked in at {$timeIn} and departed at {$timeOut}." : ($timeIn ? "Arrived safely and clocked in at {$timeIn}." : "Marked Present today."),
            'Late'    => $timeIn ? "Arrived late at {$timeIn}." : "Marked Late today.",
            'Excused' => "Absence excused ({$mark->note}).",
            'Absent'  => "Marked Absent today.",
            default   => "Status recorded as {$status}."
        };

        return [
            'has_record'   => true,
            'status'       => $timeOut ? 'Departed' : $status,
            'raw_status'   => $status,
            'badge_class'  => $badgeClass,
            'time_in'      => $timeIn,
            'time_out'     => $timeOut,
            'source'       => $source,
            'is_biometric' => $isBiometric,
            'note'         => $mark->note,
            'message'      => $message,
        ];
    }

    #[Computed]
    public function attendanceHistory(): Collection
    {
        if (! $this->selectedChild) return collect();

        return AttendanceMark::where('student_id', $this->selectedChild->id)
            ->whereHas('sheet', fn ($q) => $q->where('term', $this->term)->where('session', $this->session))
            ->with(['sheet.takenBy'])
            ->get()
            ->sortByDesc(fn ($m) => $m->sheet->date)
            ->values();
    }

    #[Computed]
    public function attendanceCalendarData(): array
    {
        if (! $this->selectedChild) {
            return ['month' => now()->format('F Y'), 'weeks' => [], 'is_current' => true, 'month_ym' => now()->format('Y-m')];
        }

        $monthStr = $this->attendanceMonth ?: now()->format('Y-m');
        try {
            $startOfMonth = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        } catch (\Throwable) {
            $startOfMonth = now()->startOfMonth();
            $monthStr = $startOfMonth->format('Y-m');
        }

        $endOfMonth   = (clone $startOfMonth)->endOfMonth();
        $monthLabel   = $startOfMonth->format('F Y');

        $marks = AttendanceMark::where('student_id', $this->selectedChild->id)
            ->whereHas('sheet', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            })
            ->with('sheet.takenBy')
            ->get()
            ->keyBy(fn ($m) => Carbon::parse($m->sheet->date)->toDateString());

        $excuses = AttendanceExcuse::where('student_id', $this->selectedChild->id)
            ->where('start_date', '<=', $endOfMonth->toDateString())
            ->where('end_date', '>=', $startOfMonth->toDateString())
            ->get();

        $daysInMonth = $startOfMonth->daysInMonth;
        $startDayOfWeek = $startOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)

        $calendarDays = [];

        // Leading empty slots for proper alignment to Monday
        for ($i = 1; $i < $startDayOfWeek; $i++) {
            $calendarDays[] = [
                'day'        => null,
                'date'       => null,
                'is_current' => false,
                'status'     => 'blank',
            ];
        }

        $todayStr = now()->toDateString();

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = (clone $startOfMonth)->day($d);
            $dateStr = $dateObj->toDateString();
            $isWeekend = $dateObj->isWeekend();
            $isToday = ($dateStr === $todayStr);
            $isFuture = $dateObj->isFuture() && !$isToday;

            $mark = $marks->get($dateStr);
            $excuse = $excuses->first(fn ($e) => $dateStr >= $e->start_date->toDateString() && $dateStr <= $e->end_date->toDateString());

            $status = 'unmarked';
            $timeIn = null;
            $timeOut = null;
            $source = null;

            if ($mark) {
                $status = strtolower($mark->status); // present, late, absent, excused
                $timeIn = $mark->arrived_at ? Carbon::parse($mark->arrived_at)->format('h:i A') : null;
                $timeOut = $mark->departed_at ? Carbon::parse($mark->departed_at)->format('h:i A') : null;
                $isBio = str_contains(strtolower($mark->note ?? ''), 'k40') || str_contains(strtolower($mark->note ?? ''), 'biometric');
                $source = $isBio ? 'K40 Biometrics' : ($mark->sheet?->takenBy?->name ?? 'Teacher Register');
            } elseif ($excuse) {
                $status = 'excused';
                $source = "Parent Note: {$excuse->reason}";
            } elseif ($isWeekend) {
                $status = 'weekend';
            } elseif ($isFuture) {
                $status = 'future';
            }

            $calendarDays[] = [
                'day'        => $d,
                'date'       => $dateStr,
                'day_name'   => $dateObj->format('D'),
                'is_current' => true,
                'is_weekend' => $isWeekend,
                'is_future'  => $isFuture,
                'is_today'   => $isToday,
                'status'     => $status,
                'time_in'    => $timeIn,
                'time_out'   => $timeOut,
                'source'     => $source,
                'note'       => $mark?->note ?? ($excuse?->reason ?? null),
            ];
        }

        $weeks = array_chunk($calendarDays, 7);

        return [
            'month'      => $monthLabel,
            'month_ym'   => $monthStr,
            'weeks'      => $weeks,
            'is_current' => ($monthStr === now()->format('Y-m')),
        ];
    }

    #[Computed]
    public function childAbsenceRequests(): Collection
    {
        if (! $this->selectedChild) return collect();

        return AttendanceExcuse::where('student_id', $this->selectedChild->id)
            ->latest('created_at')
            ->take(10)
            ->get();
    }

    public function prevAttendanceMonth(): void
    {
        $current = Carbon::createFromFormat('Y-m', $this->attendanceMonth ?: now()->format('Y-m'))->startOfMonth();
        $this->attendanceMonth = $current->subMonth()->format('Y-m');
    }

    public function nextAttendanceMonth(): void
    {
        $current = Carbon::createFromFormat('Y-m', $this->attendanceMonth ?: now()->format('Y-m'))->startOfMonth();
        $this->attendanceMonth = $current->addMonth()->format('Y-m');
    }

    public function setAttendanceMonth(string $ym): void
    {
        $this->attendanceMonth = $ym;
    }

    public function openAbsenceModal(): void
    {
        $this->absenceStartDate = now()->toDateString();
        $this->absenceEndDate   = now()->toDateString();
        $this->absenceReason    = 'Illness / Medical';
        $this->absenceNotes     = '';
        $this->showAbsenceModal = true;
    }

    public function closeAbsenceModal(): void
    {
        $this->showAbsenceModal = false;
        $this->resetValidation();
    }

    public function submitAbsenceRequest(): void
    {
        if (! $this->selectedChild) {
            return;
        }

        $this->validate([
            'absenceStartDate' => 'required|date',
            'absenceEndDate'   => 'required|date|after_or_equal:absenceStartDate',
            'absenceReason'    => 'required|string|max:100',
            'absenceNotes'     => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        AttendanceExcuse::create([
            'tenant_id'   => $user->tenant_id ?? 1,
            'student_id'  => $this->selectedChild->id,
            'parent_id'   => $user->id,
            'start_date'  => $this->absenceStartDate,
            'end_date'    => $this->absenceEndDate,
            'reason'      => $this->absenceReason,
            'notes'       => $this->absenceNotes ?: null,
            'status'      => 'pending',
        ]);

        $this->showAbsenceModal = false;
        $this->dispatch('alert', message: 'Absence request submitted successfully. The school and teachers have been notified.', type: 'success');
    }

    public function deleteAbsenceRequest(int $id): void
    {
        $excuse = AttendanceExcuse::where('id', $id)
            ->where('parent_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($excuse) {
            $excuse->delete();
            $this->dispatch('alert', message: 'Absence request removed.', type: 'info');
        }
    }

    public function selectChild(int $id): void
    {
        $this->selectedChildId = $id;
        $this->selectedTopicSubjectId = null;
        $this->activeTab = 'overview';
    }

    #[Computed]
    public function childSubjects(): Collection
    {
        if (! $this->selectedChild) return collect();
        return SchoolClass::allSubjectsForClass($this->selectedChild->class_id);
    }

    #[Computed]
    public function subjectTopics(): Collection
    {
        if (! $this->selectedChild) return collect();

        $query = \App\Models\SubjectTopic::query()
            ->where('class_id', $this->selectedChild->class_id)
            ->where('term', $this->term);

        if (! empty($this->session)) {
            $query->where('session', $this->session);
        }

        if ($this->selectedTopicSubjectId) {
            $query->where('subject_id', $this->selectedTopicSubjectId);
        }

        return $query->with('subject')
            ->orderByRaw('week_number IS NULL, week_number ASC')
            ->orderBy('id', 'asc')
            ->get();
    }

    #[Computed]
    public function topicStats(): array
    {
        $topics = $this->subjectTopics;
        $total = $topics->count();
        $completed = $topics->where('status', 'completed')->count();
        $inProgress = $topics->where('status', 'in_progress')->count();
        $upcoming = $topics->where('status', 'upcoming')->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return compact('total', 'completed', 'inProgress', 'upcoming', 'percent');
    }

    #[Computed]
    public function curriculumDocument(): ?\App\Models\CurriculumDocument
    {
        if (! $this->selectedChild || ! $this->selectedTopicSubjectId) return null;

        return \App\Models\CurriculumDocument::forClassSubjectTerm(
            $this->selectedChild->class_id,
            $this->selectedTopicSubjectId,
            $this->term,
            $this->session
        )->latest()->first();
    }

    #[Computed]
    public function classCurriculumDocuments(): Collection
    {
        if (! $this->selectedChild) return collect();

        return \App\Models\CurriculumDocument::query()
            ->where('class_id', $this->selectedChild->class_id)
            ->where('term', $this->term)
            ->when(! empty($this->session), fn ($q) => $q->where('session', $this->session))
            ->with('subject')
            ->get();
    }

    #[Computed]
    public function announcements(): Collection
    {
        return \App\Models\Announcement::query()
            ->whereIn('audience', ['parent', 'all'])
            ->latest('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function timetable(): Collection
    {
        if (! $this->selectedChild) return collect();
        return \App\Models\TimetableEntry::where('class_id', $this->selectedChild->class_id)
            ->with(['subject', 'teacher'])
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();
    }

    #[Computed]
    public function transactions(): Collection
    {
        if (! $this->selectedChild) return collect();
        return Transaction::where('student_id', $this->selectedChild->id)
            ->where('session', $this->session)
            ->orderByDesc('date')
            ->get();
    }

    #[Computed]
    public function classTeachers(): Collection
    {
        if (! $this->selectedChild || ! $this->selectedChild->class_id) {
            return collect();
        }

        $classId = (int) $this->selectedChild->class_id;

        // 1. Get teachers via Subject Allocations
        $allocations = \App\Models\SubjectAllocation::where('class_id', $classId)
            ->with(['teacher', 'subject'])
            ->get();

        $teachers = $allocations->groupBy('teacher_id')->map(function ($group) {
            $teacher = $group->first()->teacher;
            if (!$teacher) {
                return null;
            }
            $teacher->assigned_subjects = $group->pluck('subject.name')->filter()->unique()->values()->all();
            return $teacher;
        })->filter()->values();

        // 2. Also check if any timetable entries include teachers not in subject allocations
        $timetableTeachers = \App\Models\TimetableEntry::where('class_id', $classId)
            ->whereNotNull('teacher_id')
            ->with(['teacher', 'subject'])
            ->get();

        $existingTeacherIds = $teachers->pluck('id')->all();
        $missingGroups = $timetableTeachers->whereNotIn('teacher_id', $existingTeacherIds)->groupBy('teacher_id');

        foreach ($missingGroups as $tGroup) {
            $t = $tGroup->first()->teacher;
            if ($t) {
                $t->assigned_subjects = $tGroup->pluck('subject.name')->filter()->unique()->values()->all();
                if (empty($t->assigned_subjects)) {
                    $t->assigned_subjects = ['Class Instructor'];
                }
                $teachers->push($t);
            }
        }

        // 3. Fallback: If no teachers found for this class, check if any teachers are marked as class teacher in the school
        if ($teachers->isEmpty()) {
            $designated = \App\Models\User::where('role', 'teacher')
                ->where('is_class_teacher', true)
                ->where('is_active', true)
                ->get()
                ->map(function ($t) {
                    $t->assigned_subjects = ['Class Teacher'];
                    return $t;
                });
            if ($designated->isNotEmpty()) {
                $teachers = $designated;
            }
        }

        return $teachers;
    }


    private function defaultSession(): string
    {
        $y = (int) now()->format('Y');
        return "{$y}/".($y + 1);
    }

    public function render()
    {
        abort_unless(auth()->user()?->isParent(), 403);
        return view('livewire.parents.dashboard');
    }
}
