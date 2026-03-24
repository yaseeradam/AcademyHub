<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Score;
use App\Models\Transaction;
use App\Models\SchoolClass;
use App\Models\AttendanceMark;
use App\Models\CbtAttempt;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportController extends Controller
{
    public function exportPerformanceData(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasPermission('analytics.view'), 403);

        $classId = $request->query('class_id');
        $session = $request->query('session', $this->getCurrentSession());
        $term = (int) $request->query('term', $this->getCurrentTerm());

        $filename = 'performance_analytics_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($classId, $session, $term) {
            $out = fopen('php://output', 'wb');

            // Headers
            fputcsv($out, [
                'Student ID',
                'Student Name',
                'Class',
                'Subject',
                'CA1',
                'CA2',
                'Exam',
                'Total',
                'Grade',
                'Percentage',
                'Session',
                'Term'
            ]);

            // Query scores with student and subject data
            $query = Score::query()
                ->with(['student.schoolClass', 'subject'])
                ->where('session', $session)
                ->where('term', $term);

            if ($classId) {
                $query->where('class_id', $classId);
            }

            $maxPossible = config('myacademy.results_ca1_max', 20) + 
                          config('myacademy.results_ca2_max', 20) + 
                          config('myacademy.results_exam_max', 60);

            foreach ($query->cursor() as $score) {
                $percentage = $maxPossible > 0 ? round(($score->total / $maxPossible) * 100, 2) : 0;
                $grade = Score::gradeForTotal($score->total, $maxPossible);

                fputcsv($out, [
                    $score->student->admission_number,
                    $score->student->full_name,
                    $score->student->schoolClass?->name,
                    $score->subject?->name,
                    $score->ca1,
                    $score->ca2,
                    $score->exam,
                    $score->total,
                    $grade,
                    $percentage,
                    $score->session,
                    $score->term,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function exportAttendanceData(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasPermission('analytics.view'), 403);

        $classId = $request->query('class_id');
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));

        $filename = 'attendance_analytics_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($classId, $startDate, $endDate) {
            $out = fopen('php://output', 'wb');

            // Headers
            fputcsv($out, [
                'Student ID',
                'Student Name',
                'Class',
                'Date',
                'Status',
                'Marked By',
                'Time Marked'
            ]);

            // Query attendance marks
            $query = AttendanceMark::query()
                ->with(['student.schoolClass', 'markedBy'])
                ->whereBetween('date', [$startDate, $endDate]);

            if ($classId) {
                $query->whereHas('student', fn($q) => $q->where('class_id', $classId));
            }

            foreach ($query->cursor() as $mark) {
                fputcsv($out, [
                    $mark->student->admission_number,
                    $mark->student->full_name,
                    $mark->student->schoolClass?->name,
                    $mark->date,
                    $mark->status,
                    $mark->markedBy?->name,
                    $mark->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function exportFinancialData(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['admin', 'bursar'], true), 403);

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));

        $filename = 'financial_analytics_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($startDate, $endDate) {
            $out = fopen('php://output', 'wb');

            // Headers
            fputcsv($out, [
                'Transaction ID',
                'Student ID',
                'Student Name',
                'Type',
                'Amount',
                'Payment Method',
                'Description',
                'Receipt Number',
                'Date',
                'Recorded By'
            ]);

            // Query transactions
            $transactions = Transaction::query()
                ->with(['student', 'recordedBy'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('created_at')
                ->cursor();

            foreach ($transactions as $transaction) {
                fputcsv($out, [
                    $transaction->id,
                    $transaction->student?->admission_number,
                    $transaction->student?->full_name,
                    $transaction->type,
                    $transaction->amount,
                    $transaction->payment_method ?: 'Cash',
                    $transaction->description,
                    $transaction->receipt_number,
                    $transaction->created_at?->format('Y-m-d H:i:s'),
                    $transaction->recordedBy?->name,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function exportCbtData(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasPermission('analytics.view'), 403);

        $classId = $request->query('class_id');
        $examId = $request->query('exam_id');

        $filename = 'cbt_analytics_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($classId, $examId) {
            $out = fopen('php://output', 'wb');

            // Headers
            fputcsv($out, [
                'Student ID',
                'Student Name',
                'Class',
                'Exam Title',
                'Subject',
                'Started At',
                'Submitted At',
                'Duration (minutes)',
                'Score',
                'Max Score',
                'Percentage',
                'Status',
                'IP Address'
            ]);

            // Query CBT attempts
            $query = CbtAttempt::query()
                ->with(['student.schoolClass', 'exam.subject']);

            if ($classId) {
                $query->whereHas('student', fn($q) => $q->where('class_id', $classId));
            }

            if ($examId) {
                $query->where('exam_id', $examId);
            }

            foreach ($query->cursor() as $attempt) {
                $duration = null;
                if ($attempt->started_at && $attempt->submitted_at) {
                    $duration = $attempt->started_at->diffInMinutes($attempt->submitted_at);
                }

                $status = 'Not Started';
                if ($attempt->terminated_at) {
                    $status = 'Terminated';
                } elseif ($attempt->submitted_at) {
                    $status = 'Completed';
                } elseif ($attempt->started_at) {
                    $status = 'In Progress';
                }

                fputcsv($out, [
                    $attempt->student->admission_number,
                    $attempt->student->full_name,
                    $attempt->student->schoolClass?->name,
                    $attempt->exam?->title,
                    $attempt->exam?->subject?->name,
                    $attempt->started_at?->format('Y-m-d H:i:s'),
                    $attempt->submitted_at?->format('Y-m-d H:i:s'),
                    $duration,
                    $attempt->score,
                    $attempt->max_score,
                    $attempt->percent,
                    $status,
                    $attempt->ip_address,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function getCurrentSession(): string
    {
        $active = AcademicSession::activeName();
        if ($active) {
            return $active;
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }

    private function getCurrentTerm(): int
    {
        return AcademicTerm::activeTermNumber() ?? 1;
    }
}