<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\FeeStructure;
use App\Models\ProcurementRecord;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\TeacherAttendanceSheet;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ReportCardService;
use App\Support\TenantSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UtilityController extends Controller
{
    public function welcome()
    {
        if (config('academyhub.mode') === 'cbt') {
            return redirect()->route('cbt.student');
        }

        return redirect()->route('login');
    }

    public function home()
    {
        if (config('academyhub.mode') === 'cbt') {
            return redirect()->route('cbt.student');
        }

        return auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }

    public function csrfToken()
    {
        if (!auth()->check() && !session()->has('student_id')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => csrf_token()]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        \Illuminate\Support\Facades\Log::debug('UtilityController: Dashboard hit', [
            'auth_check' => auth()->check(),
            'user_id' => $user?->id,
            'email' => $user?->email,
            'role' => $user?->role,
            'tenant_id' => $user?->tenant_id,
            'resolved_tenant_id' => \App\Support\TenantSettings::tenantId(),
        ]);

        if ($user?->role === 'admin') {
            return view('pages.dashboard');
        }
        if ($user?->role === 'teacher') {
            return view('pages.dashboard-teacher');
        }
        if ($user?->role === 'parent') {
            return redirect()->route('parents.dashboard');
        }
        if ($user?->role === 'bursar') {
            return view('pages.dashboard-bursar');
        }
        if ($user?->role === 'proprietor') {
            return view('pages.dashboard-proprietor', $this->getProprietorDashboardData($user));
        }

        return view('pages.dashboard');
    }

    public function getProprietorDashboardData(User $user): array
    {
        $termNumber = AcademicTerm::activeTermNumber() ?? 1;
        $sessionName = AcademicTerm::activeSessionName() ?? date('Y') . '/' . (date('Y') + 1);

        // 1. Enrollment & Staff Counts
        $totalStudents  = Student::query()->count();
        $maleStudents   = Student::query()->where('gender', 'Male')->count();
        $femaleStudents = Student::query()->where('gender', 'Female')->count();
        $totalTeachers  = User::query()->where('role', 'teacher')->count();
        $totalClasses   = SchoolClass::query()->count();

        // 2. Financial Metrics
        $totalCollected = (float) Transaction::query()
            ->where('type', 'Income')
            ->where('is_void', false)
            ->sum('amount_paid');

        // Calculate Expected Fees across elapsed terms in the session using BillingService
        $studentsPerClass = Student::query()
            ->where('status', 'Active')
            ->select('class_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('class_id')
            ->pluck('count', 'class_id');

        $totalExpected = 0.0;
        $maxTerm = max(1, min(3, (int) $termNumber));

        foreach ($studentsPerClass as $classId => $classCount) {
            if ($classCount <= 0 || !$classId) continue;
            for ($t = 1; $t <= $maxTerm; $t++) {
                $termFee = \App\Support\BillingService::resolveFeeAmount((int) $classId, 'Tuition', $t, $sessionName);
                $totalExpected += ($termFee * $classCount);
            }
        }

        // Fallback default estimation if fee structures are not set
        if ($totalExpected <= 0 && $totalStudents > 0) {
            $totalExpected = $totalStudents * 50000.0 * $maxTerm;
        }

        $outstandingDebt = max(0.0, $totalExpected - $totalCollected);
        $collectionRate  = $totalExpected > 0 ? min(100, round(($totalCollected / $totalExpected) * 100, 1)) : 0;

        // Procurement & Expenses (strictly tenant-scoped)
        $tenantId = \App\Support\TenantSettings::tenantId();
        $procurementExpenses = (float) ProcurementRecord::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->sum('total_amount');
        $transactionExpenses = (float) Transaction::query()
            ->where('type', 'Expense')
            ->where('is_void', false)
            ->sum('amount_paid');
        $totalExpenses = $procurementExpenses + $transactionExpenses;
        $netPosition   = $totalCollected - $totalExpenses;

        // 3. Academic Analytics: Star Performers & Academic Watchlist
        $scoresQuery = Score::query()->with(['student', 'student.schoolClass', 'subject']);
        if ($sessionName) {
            $scoresQuery->where('session', $sessionName);
        }
        if ($termNumber) {
            $scoresQuery->where('term', $termNumber);
        }
        $scores = $scoresQuery->get();

        $studentStats = collect();
        $classStats = collect();
        $subjectStats = collect();

        if ($scores->isNotEmpty()) {
            // Preload classes into memory map to avoid N+1 queries
            $allClasses = SchoolClass::all()->keyBy('id');

            // Group by Student
            $byStudent = $scores->groupBy('student_id');
            foreach ($byStudent as $studentId => $studentScores) {
                $student = $studentScores->first()->student;
                if (!$student) continue;

                $avgScore = round($studentScores->avg('total'), 1);
                $failedCount = $studentScores->filter(fn($s) => ($s->total ?? 0) < 40)->count();

                $studentStats->push([
                    'id'             => $student->id,
                    'name'           => $student->full_name,
                    'adm_no'         => $student->admission_number,
                    'class_name'     => $student->schoolClass?->name ?? 'Class',
                    'photo_url'      => $student->passport_photo_url,
                    'average'        => $avgScore,
                    'total_subjects' => $studentScores->count(),
                    'failed_count'   => $failedCount,
                ]);
            }

            // Group by Class
            $byClass = $scores->groupBy('class_id');
            foreach ($byClass as $classId => $classScores) {
                $cls = $classScores->first()->student?->schoolClass ?? $allClasses->get($classId);
                if (!$cls) continue;

                $classStats->push([
                    'id'             => $cls->id,
                    'name'           => $cls->name,
                    'average'        => round($classScores->avg('total'), 1),
                    'total_students' => $classScores->pluck('student_id')->unique()->count(),
                ]);
            }

            // Group by Subject
            $bySubject = $scores->groupBy('subject_id');
            foreach ($bySubject as $subjectId => $subjectScores) {
                $sub = $subjectScores->first()->subject;
                if (!$sub) continue;

                $subjectStats->push([
                    'id'        => $sub->id,
                    'name'      => $sub->name,
                    'average'   => round($subjectScores->avg('total'), 1),
                    'pass_rate' => round(($subjectScores->filter(fn($s) => ($s->total ?? 0) >= 50)->count() / max(1, $subjectScores->count())) * 100),
                ]);
            }
        }

        $starStudents = $studentStats->sortByDesc('average')->values()->take(5);

        // Academic Watchlist: Only students with failing averages (< 50) or failed subjects (>= 1)
        $watchlistStudents = $studentStats
            ->filter(fn($s) => $s['average'] < 50 || $s['failed_count'] > 0)
            ->sortBy('average')
            ->values()
            ->take(5);

        $classRankings = $classStats->sortByDesc('average')->values();
        $bestSubjects  = $subjectStats->sortByDesc('average')->values()->take(3);

        // Struggling Subjects: Subjects with average < 50 or pass rate < 60%
        $strugglingSubjects = $subjectStats
            ->filter(fn($sub) => $sub['average'] < 50 || $sub['pass_rate'] < 60)
            ->sortBy('average')
            ->values()
            ->take(3);

        // 4. Staff Attendance (Today & Dual-Shift)
        $todayStr = now()->toDateString();
        $todaySheet = TeacherAttendanceSheet::query()->where('date', $todayStr)->first();
        $marks = $todaySheet ? $todaySheet->marks()->with('teacher')->get() : collect();

        $presentTeachers = $marks->where('status', 'Present')->count();
        $lateTeachers    = $marks->where('status', 'Late')->count();
        $absentTeachers  = $marks->where('status', 'Absent')->count();
        $markedCount     = $marks->count();

        $staffPunctualityRate = $totalTeachers > 0
            ? round(($presentTeachers / max(1, $totalTeachers)) * 100)
            : 0;

        // Shift breakdown
        $westernStaffCount = 0;
        $islamicStaffCount = 0;
        $teachers = User::query()->where('role', 'teacher')->get();
        foreach ($teachers as $t) {
            if ($t->getShift() === 'Islamic') {
                $islamicStaffCount++;
            } else {
                $westernStaffCount++;
            }
        }

        return [
            'term'                 => $termNumber,
            'session'              => $sessionName,
            'totalStudents'        => $totalStudents,
            'maleStudents'         => $maleStudents,
            'femaleStudents'       => $femaleStudents,
            'totalTeachers'        => $totalTeachers,
            'totalClasses'         => $totalClasses,
            'totalCollected'       => $totalCollected,
            'totalExpected'        => $totalExpected,
            'outstandingDebt'      => $outstandingDebt,
            'collectionRate'       => $collectionRate,
            'totalExpenses'        => $totalExpenses,
            'netPosition'          => $netPosition,
            'starStudents'         => $starStudents,
            'watchlistStudents'    => $watchlistStudents,
            'classRankings'        => $classRankings,
            'bestSubjects'         => $bestSubjects,
            'strugglingSubjects'   => $strugglingSubjects,
            'presentTeachers'      => $presentTeachers,
            'lateTeachers'         => $lateTeachers,
            'absentTeachers'       => $absentTeachers,
            'markedCount'          => $markedCount,
            'staffPunctualityRate' => $staffPunctualityRate,
            'westernStaffCount'    => $westernStaffCount,
            'islamicStaffCount'    => $islamicStaffCount,
            'recentMarks'          => $marks->take(6),
        ];
    }

    public function studentReportCard(Request $request, ReportCardService $reportCardService)
    {
        $studentId = session('student_id');
        abort_unless($studentId, 403);

        $student = Student::with(['schoolClass', 'section'])->find($studentId);
        abort_unless($student, 403);

        $term = (int) $request->query('term', AcademicTerm::activeTermNumber());
        $session = (string) $request->query('session', AcademicTerm::activeSessionName() ?? '');

        abort_unless($term >= 1 && $term <= 3, 422);

        $published = ResultPublication::where('class_id', $student->class_id)
            ->where('term', $term)->where('session', $session)
            ->whereNotNull('published_at')->exists();
        abort_unless($published, 403);

        $data = $reportCardService->build($student, $term, $session);

        $template = (string) config('academyhub.report_card_template', 'compact');
        $view = ReportCardService::viewForTemplate($template);

        $pdf = Pdf::loadView($view, $data)->setPaper('a4');
        $filename = 'report-card-' . $student->admission_number . '-' . $session . '-T' . $term . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function cbtSampleDownload()
    {
        $content = "1. What is the powerhouse of the cell?\nA. Nucleus\nB. Mitochondria\nC. Ribosome\nD. Golgi body\nANS: B\nMARKS: 2\n\n2. Which planet is closest to the sun?\nA. Earth\nB. Venus\nC. Mercury\nD. Mars\nANS: C\nMARKS: 1\n\n3. What is the chemical symbol for water?\nA. CO2\nB. H2O\nC. NaCl\nD. O2\nANS: B\nMARKS: 1\n\n4. Define photosynthesis and explain its importance to plants.\nTYPE: theory\nMARKS: 5\n\n5. Describe the water cycle.\nTYPE: theory\nMARKS: 4\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="sample_questions.txt"',
        ]);
    }

    public function settingsDebug()
    {
        $cacheKey = TenantSettings::settingsCacheKey();
        $settingsPath = TenantSettings::settingsPath();

        return response()->json([
            'config_values' => [
                'rc_show_position' => config('academyhub.rc_show_position'),
                'rc_show_attendance' => config('academyhub.rc_show_attendance'),
                'rc_show_next_term_date' => config('academyhub.rc_show_next_term_date'),
                'rc_show_teacher_remarks' => config('academyhub.rc_show_teacher_remarks'),
                'rc_show_principal_remarks' => config('academyhub.rc_show_principal_remarks'),
            ],
            'cache_key' => $cacheKey,
            'cache_key_exists' => Cache::has($cacheKey),
            'settings_file_path' => $settingsPath,
            'settings_file_exists' => file_exists($settingsPath),
            'settings_file_content' => file_exists($settingsPath)
                ? json_decode(file_get_contents($settingsPath), true)
                : null,
        ]);
    }

    public function logClientError(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'message'    => 'required|string|max:500',
            'source'     => 'nullable|string|max:255',
            'line'       => 'nullable|integer',
            'col'        => 'nullable|integer',
            'stack'      => 'nullable|string|max:2000',
            'url'        => 'nullable|string|max:500',
        ]);

        \Illuminate\Support\Facades\Log::error('CLIENT JS ERROR DETECTED', [
            'message'    => $request->input('message'),
            'source'     => $request->input('source'),
            'line'       => $request->input('line'),
            'col'        => $request->input('col'),
            'stack'      => $request->input('stack'),
            'url'        => $request->input('url'),
            'user_agent' => substr((string)$request->header('User-Agent'), 0, 255),
            'user'       => auth()->user()->email,
        ]);

        return response()->json(['success' => true]);
    }
}
