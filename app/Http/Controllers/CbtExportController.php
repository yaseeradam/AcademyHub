<?php

namespace App\Http\Controllers;

use App\Models\CbtExam;
use App\Models\CbtAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CbtExportController extends Controller
{
    public function examResults(CbtExam $exam): StreamedResponse
    {
        $answeredSub = DB::table('cbt_answers')
            ->selectRaw('attempt_id, count(*) as answered')
            ->where(function ($q) {
                $q->whereNotNull('option_id')
                    ->orWhereNotNull('text_answer');
            })
            ->groupBy('attempt_id');

        $query = DB::table('students as s')
            ->leftJoin('cbt_attempts as a', function ($join) use ($exam) {
                $join->on('a.student_id', '=', 's.id')
                    ->where('a.exam_id', '=', $exam->id);
            })
            ->leftJoinSub($answeredSub, 'ans', function ($join) {
                $join->on('ans.attempt_id', '=', 'a.id');
            })
            ->where('s.class_id', '=', $exam->class_id)
            ->where('s.status', '=', 'Active')
            ->orderBy('s.first_name')
            ->orderBy('s.last_name')
            ->select([
                's.admission_number',
                's.first_name',
                's.last_name',
                'a.started_at',
                'a.submitted_at',
                'a.terminated_at',
                'a.last_activity_at',
                'a.score',
                'a.max_score',
                'a.percent',
                'a.ip_address',
                'a.allowed_ip',
                DB::raw('COALESCE(ans.answered, 0) as answered'),
            ]);

        $totalQuestions = (int) $exam->questions()->count();
        $filename = 'cbt-exam-'.$exam->id.'-results-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $totalQuestions) {
            $out = fopen('php://output', 'wb');

            fputcsv($out, [
                'admission_number',
                'student_name',
                'state',
                'answered',
                'remaining',
                'score',
                'max_score',
                'percent',
                'started_at',
                'submitted_at',
                'last_activity_at',
                'ip_address',
                'allowed_ip',
            ]);

            foreach ($query->cursor() as $row) {
                $state = 'not_started';
                if ($row->terminated_at) {
                    $state = 'terminated';
                } elseif ($row->submitted_at) {
                    $state = 'submitted';
                } elseif ($row->started_at) {
                    $state = 'in_progress';
                }

                $answered = (int) ($row->answered ?? 0);
                $remaining = max(0, $totalQuestions - $answered);

                $name = trim((string) ($row->first_name.' '.$row->last_name));

                fputcsv($out, [
                    $row->admission_number,
                    $name,
                    $state,
                    $answered,
                    $remaining,
                    $row->score !== null ? (string) $row->score : '',
                    $row->max_score !== null ? (string) $row->max_score : '',
                    $row->percent !== null ? (string) $row->percent : '',
                    $row->started_at,
                    $row->submitted_at,
                    $row->last_activity_at,
                    $row->ip_address,
                    $row->allowed_ip,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function examPdf(CbtExam $exam)
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        if ($user->role === 'teacher') {
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        $exam->load(['questions.options', 'schoolClass', 'subject']);

        $pdf = Pdf::loadView('pdf.cbt-exam', ['exam' => $exam]);
        $filename = 'exam-'.str_replace(' ', '-', strtolower($exam->title)).'.pdf';

        return $pdf->download($filename)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function exportAttemptResultPdf(CbtAttempt $attempt)
    {
        $studentId = session('student_id');
        $user = auth()->user();
        
        $isAuthorized = ($studentId && (int) $attempt->student_id === (int) $studentId)
            || ($user && in_array($user->role, ['admin', 'teacher'], true));

        abort_unless($isAuthorized, 403, 'Unauthorized access to this attempt.');
        abort_unless($attempt->submitted_at, 403, 'This attempt has not been submitted yet.');
        abort_unless($attempt->exam->exam_type === 'aptitude', 403, 'Only aptitude exam attempts can be exported to PDF.');

        $attempt->load([
            'exam.questions.options',
            'student.schoolClass',
            'answers.question.options',
            'answers.option'
        ]);

        $schoolName = config('myacademy.school_name', config('app.name', 'School'));
        $schoolAddress = config('myacademy.school_address', '');
        $schoolPhone = config('myacademy.school_phone', '');
        $schoolEmail = config('myacademy.school_email', '');
        $logoPath = config('myacademy.school_logo');

        $logoBase64 = null;
        if ($logoPath) {
            $fullPath = storage_path('app/public/' . $logoPath);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        $pdf = Pdf::loadView('pdf.cbt-attempt-result', [
            'attempt' => $attempt,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
            'logoBase64' => $logoBase64,
        ]);

        $filename = 'aptitude-result-' . str_replace(' ', '-', strtolower($attempt->student->first_name . '-' . $attempt->student->last_name)) . '.pdf';

        return $pdf->download($filename)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
