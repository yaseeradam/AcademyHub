<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Score;
use App\Models\Section;
use App\Models\Student;
use App\Models\SubjectAllocation;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /** GET /api/teacher/classes — classes assigned to the teacher (admin gets all) */
    public function classes(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $classes = \App\Models\SchoolClass::orderBy('level')->get(['id', 'name', 'level']);
        } else {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)
                ->distinct()->pluck('class_id');
            $classes = \App\Models\SchoolClass::whereIn('id', $classIds)
                ->orderBy('level')
                ->get(['id', 'name', 'level']);
        }

        return response()->json(['data' => $classes]);
    }

    /** GET /api/teacher/classes/{classId}/students */
    public function students(Request $request, int $classId)
    {
        $this->authorizeClass($request, $classId);

        $students = Student::where('class_id', $classId)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number', 'class_id', 'section_id']);

        return response()->json(['data' => $students]);
    }

    /** GET /api/teacher/classes/{classId}/subjects */
    public function subjects(Request $request, int $classId)
    {
        $this->authorizeClass($request, $classId);

        if ($request->user()->role === 'admin') {
            $subjectIds = SubjectAllocation::where('class_id', $classId)
                ->distinct()->pluck('subject_id');
            $subjects = \App\Models\Subject::whereIn('id', $subjectIds)
                ->get(['id', 'name', 'code']);
        } else {
            $subjects = SubjectAllocation::where('teacher_id', $request->user()->id)
                ->where('class_id', $classId)
                ->with('subject:id,name,code')
                ->get()
                ->pluck('subject')
                ->unique('id')
                ->values();
        }

        return response()->json(['data' => $subjects]);
    }

    /** GET /api/teacher/classes/{classId}/scores?term=1&session=2024/2025 */
    public function scores(Request $request, int $classId)
    {
        $this->authorizeClass($request, $classId);

        $term = $request->query('term', AcademicTerm::activeTermNumber());
        $session = $request->query('session', AcademicTerm::activeSessionName());

        $scores = Score::where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session)
            ->get(['id', 'student_id', 'subject_id', 'class_id', 'term', 'session', 'ca1', 'ca2', 'exam', 'total', 'grade']);

        return response()->json(['data' => $scores]);
    }

    /** GET /api/teacher/classes/{classId}/attendance?date=2024-01-15 */
    public function attendance(Request $request, int $classId)
    {
        $this->authorizeClass($request, $classId);

        $sectionId = (int) $request->query('section_id', 0);
        if ($sectionId <= 0) {
            $sectionIds = Section::query()->where('class_id', $classId)->pluck('id');
            if ($sectionIds->count() === 1) {
                $sectionId = (int) $sectionIds->first();
            } else {
                return response()->json([
                    'message' => 'section_id is required for attendance (multiple sections exist for this class).',
                ], 422);
            }
        }

        $sectionOk = Section::query()
            ->where('id', $sectionId)
            ->where('class_id', $classId)
            ->exists();
        abort_unless($sectionOk, 422, 'Invalid section_id for this class.');

        $date = $request->query('date', today()->toDateString());
        $term = $request->query('term', AcademicTerm::activeTermNumber());
        $session = $request->query('session', AcademicTerm::activeSessionName());

        $sheet = AttendanceSheet::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('date', $date)
            ->where('term', $term)
            ->where('session', $session)
            ->with('marks:id,sheet_id,student_id,status,note')
            ->first();

        return response()->json(['data' => $sheet]);
    }

    /** POST /api/teacher/attendance - save attendance sheet + marks */
    public function saveAttendance(Request $request)
    {
        $request->validate([
            'class_id'  => 'required|integer|exists:classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'date'      => 'required|date',
            'term'      => 'required|integer',
            'session'   => 'required|string',
            'marks'     => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.status'     => 'required|string',
        ]);

        $this->authorizeClass($request, $request->class_id);

        $sectionId = (int) ($request->section_id ?? 0);
        if ($sectionId <= 0) {
            $sectionIds = Section::query()->where('class_id', $request->class_id)->pluck('id');
            if ($sectionIds->count() === 1) {
                $sectionId = (int) $sectionIds->first();
            } else {
                return response()->json([
                    'message' => 'section_id is required for attendance (multiple sections exist for this class).',
                    'errors' => ['section_id' => ['The section_id field is required.']],
                ], 422);
            }
        }

        $sectionOk = Section::query()
            ->where('id', $sectionId)
            ->where('class_id', (int) $request->class_id)
            ->exists();
        abort_unless($sectionOk, 422, 'Invalid section_id for this class.');

        $sheet = AttendanceSheet::firstOrCreate(
            [
                'class_id' => $request->class_id,
                'section_id' => $sectionId,
                'date'     => $request->date,
                'term'     => $request->term,
                'session'  => $request->session,
            ],
            ['taken_by' => $request->user()->id]
        );

        foreach ($request->marks as $mark) {
            $normalizedStatus = $this->normalizeAttendanceStatus($mark['status'] ?? null);
            if (! $normalizedStatus) {
                return response()->json([
                    'message' => 'Invalid attendance status.',
                    'errors' => ['marks' => ['Status must be one of: Present, Absent, Late, Excused.']],
                ], 422);
            }

            AttendanceMark::updateOrCreate(
                ['sheet_id' => $sheet->id, 'student_id' => $mark['student_id']],
                ['status' => $normalizedStatus, 'note' => $mark['note'] ?? null]
            );
        }

        return response()->json(['data' => $sheet->load('marks'), 'message' => 'Attendance saved.']);
    }

    /** POST /api/teacher/scores — upsert scores */
    public function saveScores(Request $request)
    {
        $request->validate([
            'scores'              => 'required|array',
            'scores.*.student_id' => 'required|integer',
            'scores.*.subject_id' => 'required|integer',
            'scores.*.class_id'   => 'required|integer',
            'scores.*.term'       => 'required|integer',
            'scores.*.session'    => 'required|string',
            'scores.*.ca1'        => 'nullable|integer|min:0',
            'scores.*.ca2'        => 'nullable|integer|min:0',
            'scores.*.exam'       => 'nullable|integer|min:0',
        ]);

        foreach ($request->scores as $s) {
            $this->authorizeClass($request, $s['class_id']);
            $this->authorizeSubject($request, (int) $s['class_id'], (int) $s['subject_id']);

            Score::updateOrCreate(
                [
                    'student_id' => $s['student_id'],
                    'subject_id' => $s['subject_id'],
                    'class_id'   => $s['class_id'],
                    'term'       => $s['term'],
                    'session'    => $s['session'],
                ],
                [
                    'ca1'  => $s['ca1'] ?? 0,
                    'ca2'  => $s['ca2'] ?? 0,
                    'exam' => $s['exam'] ?? 0,
                ]
            );
        }

        return response()->json(['message' => 'Scores saved.']);
    }

    private function authorizeClass(Request $request, int $classId): void
    {
        if ($request->user()->role === 'admin') return;

        $allowed = SubjectAllocation::where('teacher_id', $request->user()->id)
            ->where('class_id', $classId)
            ->exists();

        abort_unless($allowed, 403, 'Not assigned to this class.');
    }

    private function authorizeSubject(Request $request, int $classId, int $subjectId): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        $allowed = SubjectAllocation::query()
            ->where('teacher_id', $request->user()->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();

        abort_unless($allowed, 403, 'Not assigned to this subject.');
    }

    private function normalizeAttendanceStatus(mixed $status): ?string
    {
        if (!is_string($status)) {
            return null;
        }

        $normalized = ucfirst(mb_strtolower(trim($status)));

        return in_array($normalized, ['Present', 'Absent', 'Late', 'Excused'], true)
            ? $normalized
            : null;
    }
}
