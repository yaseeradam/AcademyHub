<?php

namespace App\Console\Commands;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceExcuse;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkAbsentStudents extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan attendance:mark-absent
     *        php artisan attendance:mark-absent --date=2026-11-06
     *        php artisan attendance:mark-absent --class=1
     *        php artisan attendance:mark-absent --force
     */
    protected $signature = 'attendance:mark-absent 
                            {--date= : Specific date to reconcile (YYYY-MM-DD, default today)}
                            {--class= : Specific class ID}
                            {--section= : Specific section ID}
                            {--tenant= : Specific tenant ID}
                            {--force : Force marking even on weekends}';

    protected $description = 'Automatically record unpunched and missing students as Absent for a school day';

    public function handle(): int
    {
        $dateStr = $this->option('date') ?: now()->toDateString();
        $date = Carbon::parse($dateStr);
        $force = (bool) $this->option('force');

        // Skip weekends unless forced
        if ($date->isWeekend() && !$force) {
            $this->info("Date {$dateStr} is a weekend ({$date->format('l')}). Skipping absent marking (use --force to override).");
            return 0;
        }

        $activeTerm = AcademicTerm::active();
        if (!$activeTerm && !$force) {
            $this->warn("No active academic term configured. Skipping absent reconciliation to prevent false absences during school vacation (use --force to override).");
            return 0;
        }

        // Check if date falls outside active term dates
        if ($activeTerm && !$force) {
            if ($activeTerm->start_date && $date->lt(Carbon::parse($activeTerm->start_date)->startOfDay())) {
                $this->info("Date {$dateStr} is prior to the start of active Term {$activeTerm->term_number} ({$activeTerm->start_date->format('Y-m-d')}). Skipping absent reconciliation.");
                return 0;
            }
            if ($activeTerm->end_date && $date->gt(Carbon::parse($activeTerm->end_date)->endOfDay())) {
                $this->info("Date {$dateStr} is after the end of active Term {$activeTerm->term_number} ({$activeTerm->end_date->format('Y-m-d')}). Skipping absent reconciliation.");
                return 0;
            }
        }

        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? (date('Y') . '/' . (date('Y') + 1));

        $systemUserId = User::where('role', 'admin')->orderBy('id')->value('id') 
            ?? User::orderBy('id')->value('id') 
            ?? 1;

        $targetClassId = $this->option('class') ? (int) $this->option('class') : null;
        $targetSectionId = $this->option('section') ? (int) $this->option('section') : null;
        $targetTenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;

        $classesQuery = SchoolClass::query();
        if ($targetClassId) {
            $classesQuery->where('id', $targetClassId);
        }
        if ($targetTenantId) {
            $classesQuery->where('tenant_id', $targetTenantId);
        }
        $classes = $classesQuery->get();

        if ($classes->isEmpty()) {
            $this->warn("No classes found matching the criteria.");
            return 0;
        }

        $this->info("Running attendance reconciliation for date: {$dateStr} (Term {$termNumber}, Session {$sessionName})...");

        $totalAbsent = 0;
        $totalExcused = 0;
        $totalProtected = 0;
        $sheetsCount = 0;

        foreach ($classes as $class) {
            // Find sections belonging to this class
            $sectionsQuery = Section::where('class_id', $class->id);
            if ($targetSectionId) {
                $sectionsQuery->where('id', $targetSectionId);
            }
            $sections = $sectionsQuery->get();

            // If no sections explicitly exist, create a virtual single section iteration
            $sectionList = $sections->isNotEmpty() ? $sections : collect([(object) ['id' => 0, 'name' => 'General']]);

            foreach ($sectionList as $sec) {
                $secId = $sec->id > 0 ? (int) $sec->id : null;

                // Find all active students in this class and section
                $studentsQuery = Student::where('class_id', $class->id)
                    ->where('status', 'Active');
                if ($secId) {
                    $studentsQuery->where('section_id', $secId);
                }
                $students = $studentsQuery->get();

                if ($students->isEmpty()) {
                    continue;
                }

                // If secId is null, use the first section of the student or fallback to section 1
                $sheetSectionId = $secId ?: ($students->first()?->section_id ?: 1);

                // Find or create AttendanceSheet for this class & section
                $sheet = AttendanceSheet::withoutGlobalScopes()->firstOrCreate(
                    [
                        'class_id'   => $class->id,
                        'section_id' => $sheetSectionId,
                        'date'       => $dateStr,
                        'term'       => $termNumber,
                        'session'    => $sessionName,
                    ],
                    [
                        'tenant_id'  => $class->tenant_id ?? 1,
                        'taken_by'   => $systemUserId,
                    ]
                );
                $sheetsCount++;

                // Load all existing marks for this sheet
                $existingMarks = AttendanceMark::withoutGlobalScopes()
                    ->where('sheet_id', $sheet->id)
                    ->get()
                    ->keyBy('student_id');

                DB::transaction(function () use (
                    $sheet,
                    $students,
                    $existingMarks,
                    $dateStr,
                    &$totalAbsent,
                    &$totalExcused,
                    &$totalProtected
                ) {
                    foreach ($students as $student) {
                        $mark = $existingMarks->get($student->id);

                        // PROTECT verified attendance:
                        // If student punched biometric gate (arrived_at is set) or already marked Present/Late
                        if ($mark && (!empty($mark->arrived_at) || in_array($mark->status, ['Present', 'Late'], true))) {
                            $totalProtected++;
                            continue;
                        }

                        // If already marked Excused, leave as Excused
                        if ($mark && $mark->status === 'Excused') {
                            $totalExcused++;
                            continue;
                        }

                        // If already marked Absent, leave as Absent
                        if ($mark && $mark->status === 'Absent') {
                            continue;
                        }

                        // Check if student has an approved excuse for this date
                        $hasExcuse = AttendanceExcuse::where('student_id', $student->id)
                            ->whereDate('start_date', '<=', $dateStr)
                            ->whereDate('end_date', '>=', $dateStr)
                            ->where('status', 'Approved')
                            ->exists();

                        $newStatus = $hasExcuse ? 'Excused' : 'Absent';
                        $note = $hasExcuse 
                            ? 'Absence excused by school management'
                            : 'Absent (No biometric gate punch recorded)';

                        AttendanceMark::withoutGlobalScopes()->updateOrCreate(
                            [
                                'sheet_id'   => $sheet->id,
                                'student_id' => $student->id,
                            ],
                            [
                                'tenant_id'   => $student->tenant_id ?? $sheet->tenant_id ?? 1,
                                'status'      => $newStatus,
                                'arrived_at'  => null,
                                'departed_at' => null,
                                'note'        => $note,
                            ]
                        );

                        if ($newStatus === 'Absent') {
                            $totalAbsent++;
                        } else {
                            $totalExcused++;
                        }
                    }
                });
            }
        }

        $this->info("Reconciliation complete for {$dateStr}:");
        $this->line("  - Attendance Sheets Processed: {$sheetsCount}");
        $this->line("  - Verified Present/Late Protected: {$totalProtected}");
        $this->line("  - Missing Students Marked Absent: {$totalAbsent}");
        $this->line("  - Excused Absences: {$totalExcused}");

        Log::info("Attendance:mark-absent completed for {$dateStr}: {$totalAbsent} marked absent, {$totalExcused} excused, {$totalProtected} verified.");

        return 0;
    }
}
