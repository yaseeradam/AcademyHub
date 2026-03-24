<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SubjectAllocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentsExportController extends Controller
{
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $format = strtolower((string) $request->query('format', 'csv'));
        $classFilter = $request->query('class');
        $sectionFilter = $request->query('section');
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $from = $request->query('from');
        $to = $request->query('to');
        $includeHeaders = $request->boolean('include_headers', true);

        $query = Student::query()
            ->with(['schoolClass', 'section'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        $user = $request->user();
        $teacherClassIds = null;
        if ($user?->role === 'teacher') {
            $teacherClassIds = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->pluck('class_id')
                ->unique()
                ->values();

            if ($teacherClassIds->isEmpty()) {
                return $this->emptyExport($format, $includeHeaders);
            }

            $query->whereIn('class_id', $teacherClassIds);
        }

        if ($classFilter && $classFilter !== 'all') {
            if ($teacherClassIds && ! $teacherClassIds->contains((int) $classFilter)) {
                return $this->emptyExport($format, $includeHeaders);
            }

            $query->where('class_id', $classFilter);
        }

        if ($sectionFilter && $sectionFilter !== 'all') {
            if ($classFilter === 'all' || ! $classFilter) {
                $query->whereHas('section', fn ($q) => $q->where('name', $sectionFilter));
            } else {
                $query->where('section_id', $sectionFilter);
            }
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%");
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($format === 'json') {
            $students = $query->get()->map(fn (Student $student) => $this->formatStudent($student));

            return response()->json($students);
        }

        if ($format === 'pdf') {
            $students = $query->get()->map(fn (Student $student) => $this->formatStudent($student));
            $filename = 'students-'.now()->format('Ymd-His').'.pdf';

            return Pdf::loadView('exports.students-pdf', [
                'students' => $students,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
                'showHeaders' => $includeHeaders,
            ])->download($filename)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        if ($format === 'excel') {
            $filename = 'students-'.now()->format('Ymd-His').'.xls';

            return response()->streamDownload(function () use ($query, $includeHeaders) {
                $students = $query->get()->map(fn (Student $student) => $this->formatStudent($student));
                $this->writeExcelHtml($students, $includeHeaders);
            }, $filename, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            ]);
        }

        if ($format !== 'csv') {
            return response()->json(['message' => 'Unsupported export format.'], 422);
        }

        $filename = 'students-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $includeHeaders) {
            $out = fopen('php://output', 'wb');

            if ($includeHeaders) {
                fputcsv($out, [
                    'admission_number',
                    'first_name',
                    'last_name',
                    'full_name',
                    'class',
                    'section',
                    'gender',
                    'status',
                    'guardian_name',
                    'guardian_phone',
                    'dob',
                    'blood_group',
                    'created_at',
                ]);
            }

            foreach ($query->cursor() as $student) {
                fputcsv($out, [
                    $student->admission_number,
                    $student->first_name,
                    $student->last_name,
                    $student->full_name,
                    $student->schoolClass?->name,
                    $student->section?->name,
                    $student->gender,
                    $student->status,
                    $student->guardian_name,
                    $student->guardian_phone,
                    optional($student->dob)->format('Y-m-d'),
                    $student->blood_group,
                    optional($student->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function emptyExport(string $format, bool $includeHeaders): StreamedResponse|JsonResponse
    {
        if ($format === 'json') {
            return response()->json([]);
        }

        $filename = 'students-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($includeHeaders) {
            $out = fopen('php://output', 'wb');

            if ($includeHeaders) {
                fputcsv($out, [
                    'admission_number',
                    'first_name',
                    'last_name',
                    'full_name',
                    'class',
                    'section',
                    'gender',
                    'status',
                    'guardian_name',
                    'guardian_phone',
                    'dob',
                    'blood_group',
                    'created_at',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function formatStudent(Student $student): array
    {
        return [
            'admission_number' => $student->admission_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->full_name,
            'class' => $student->schoolClass?->name,
            'section' => $student->section?->name,
            'gender' => $student->gender,
            'status' => $student->status,
            'guardian_name' => $student->guardian_name,
            'guardian_phone' => $student->guardian_phone,
            'dob' => optional($student->dob)->format('Y-m-d'),
            'blood_group' => $student->blood_group,
            'created_at' => optional($student->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function writeExcelHtml(Collection $students, bool $includeHeaders): void
    {
        echo '<html><head><meta charset="utf-8"></head><body>';
        echo '<table border="1">';

        if ($includeHeaders) {
            echo '<tr>';
            foreach ([
                'admission_number',
                'first_name',
                'last_name',
                'full_name',
                'class',
                'section',
                'gender',
                'status',
                'guardian_name',
                'guardian_phone',
                'dob',
                'blood_group',
                'created_at',
            ] as $header) {
                echo '<th>'.e($header).'</th>';
            }
            echo '</tr>';
        }

        foreach ($students as $student) {
            echo '<tr>';
            foreach ([
                $student['admission_number'],
                $student['first_name'],
                $student['last_name'],
                $student['full_name'],
                $student['class'],
                $student['section'],
                $student['gender'],
                $student['status'],
                $student['guardian_name'],
                $student['guardian_phone'],
                $student['dob'],
                $student['blood_group'],
                $student['created_at'],
            ] as $value) {
                echo '<td>'.e((string) $value).'</td>';
            }
            echo '</tr>';
        }

        echo '</table></body></html>';
    }
}
