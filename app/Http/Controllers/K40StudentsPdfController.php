<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Support\TenantSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class K40StudentsPdfController extends Controller
{
    /**
     * Generate and download or stream the ZKTeco K40 Student ID Directory PDF.
     */
    public function download(Request $request): Response
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        TenantSettings::loadToConfig($tenantId);

        $classFilter = $request->query('class_id', $request->query('class', 'all'));
        $sectionFilter = $request->query('section_id', $request->query('section', 'all'));
        $shiftFilter = $request->query('shift', 'all');
        $statusFilter = $request->query('status', 'all'); // 'all', 'assigned', 'unassigned'
        $search = trim((string) $request->query('search', ''));
        $autoAssign = $request->boolean('auto_assign', false);
        $stream = $request->boolean('stream', false);

        // Optionally auto-assign K40 UIDs to unassigned students before generating
        if ($autoAssign) {
            $this->performAutoAssignment($tenantId, $classFilter);
        }

        $query = Student::query()
            ->with(['schoolClass', 'section'])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Graduated')->orWhere('status', 'Active');
            });

        if ($classFilter && $classFilter !== 'all') {
            $query->where('class_id', (int) $classFilter);
        }

        if ($sectionFilter && $sectionFilter !== 'all') {
            if (is_numeric($sectionFilter)) {
                $query->where('section_id', (int) $sectionFilter);
            } else {
                $query->whereHas('section', fn ($q) => $q->where('name', $sectionFilter));
            }
        }

        if ($shiftFilter && $shiftFilter !== 'all') {
            $query->where(function ($q) use ($shiftFilter) {
                $q->where('shift', $shiftFilter)
                    ->orWhere('custom_fields->shift', $shiftFilter)
                    ->orWhereHas('section', fn ($sq) => $sq->where('shift', $shiftFilter));
            });
        }

        if ($statusFilter === 'assigned') {
            $query->whereNotNull('k40_uid');
        } elseif ($statusFilter === 'unassigned') {
            $query->whereNull('k40_uid');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere('k40_uid', 'like', "%{$search}%");
            });
        }

        // Sorting: Group by Class, then Section, then K40 UID (nulls last) or Name
        $students = $query->get()->sort(function (Student $a, Student $b) {
            $classCompare = ($a->schoolClass?->level ?? 999) <=> ($b->schoolClass?->level ?? 999);
            if ($classCompare !== 0) {
                return $classCompare;
            }

            $sectionCompare = strcmp((string) ($a->section?->name ?? ''), (string) ($b->section?->name ?? ''));
            if ($sectionCompare !== 0) {
                return $sectionCompare;
            }

            $aUid = $a->k40_uid ?? 999999;
            $bUid = $b->k40_uid ?? 999999;
            if ($aUid !== $bUid) {
                return $aUid <=> $bUid;
            }

            return strcmp((string) $a->full_name, (string) $b->full_name);
        })->values();

        // School Information & Branding
        $tenant = \App\Models\Tenant::find($tenantId);
        $schoolName = config('academyhub.school_name');
        if (! $schoolName || in_array($schoolName, ['Your School Name', 'Greenwood School', 'School Name'], true)) {
            $schoolName = $tenant?->name ?: config('app.name', 'AI INTEGRATED ACADEMY ARGUNGU');
        }
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

        // Active Session & Term
        $activeTerm = AcademicTerm::active();
        $termLabel = match((int) ($activeTerm?->term_number ?? 1)) {
            1 => 'First Term',
            2 => 'Second Term',
            3 => 'Third Term',
            default => 'Term ' . ($activeTerm?->term_number ?? '1'),
        };
        $sessionLabel = AcademicSession::activeName() ?? (now()->format('Y') . '/' . (now()->year + 1));

        // Filter descriptive labels
        $selectedClass = ($classFilter && $classFilter !== 'all') ? SchoolClass::find($classFilter) : null;
        $selectedSection = ($sectionFilter && $sectionFilter !== 'all' && is_numeric($sectionFilter)) ? Section::find($sectionFilter) : null;
        $classLabel = $selectedClass ? $selectedClass->name : 'All Classes';
        if ($selectedSection) {
            $classLabel .= ' (' . $selectedSection->name . ')';
        }

        // Compute KPIs
        $totalCount = $students->count();
        $assignedCount = $students->whereNotNull('k40_uid')->count();
        $unassignedCount = $totalCount - $assignedCount;
        $coveragePercent = $totalCount > 0 ? (int) round(($assignedCount / $totalCount) * 100) : 0;

        $pdf = Pdf::loadView('pdf.students-k40-directory', [
            'students'        => $students,
            'totalCount'      => $totalCount,
            'assignedCount'   => $assignedCount,
            'unassignedCount' => $unassignedCount,
            'coveragePercent' => $coveragePercent,
            'classLabel'      => $classLabel,
            'shiftFilter'     => $shiftFilter,
            'statusFilter'    => $statusFilter,
            'schoolName'      => $schoolName,
            'schoolMotto'     => $schoolMotto,
            'schoolAddress'   => $schoolAddress,
            'schoolPhone'     => $schoolPhone,
            'schoolEmail'     => $schoolEmail,
            'logoBase64'      => $logoBase64,
            'termLabel'       => $termLabel,
            'sessionLabel'    => $sessionLabel,
            'generatedAt'     => now()->format('d M, Y \a\t g:i A'),
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'dpi'                  => 72,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'         => true,
            'defaultFont'          => 'dejavu sans',
        ]);

        $safeScope = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $classLabel);
        $filename = "K40_Students_Directory_{$safeScope}_" . now()->format('Ymd_His') . ".pdf";

        if ($stream) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * API / Web action to automatically assign sequential K40 UIDs to unassigned students.
     */
    public function autoAssign(Request $request)
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $classFilter = $request->input('class_id', 'all');

        $assigned = self::performAutoAssignment($tenantId, $classFilter);

        $message = $assigned > 0
            ? "Successfully auto-assigned K40 Biometric IDs to {$assigned} student(s)!"
            : "All students in the selected scope already have K40 IDs assigned.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $assigned, 'message' => $message]);
        }

        return back()->with('status', $message);
    }

    /**
     * Sequentially assign unique K40 hardware UIDs starting from next available ID.
     */
    public static function performAutoAssignment(int $tenantId, string|int $classFilter = 'all'): int
    {
        return DB::transaction(function () use ($tenantId, $classFilter) {
            // Find current maximum K40 UID in the school (minimum starting base 250)
            $currentMax = (int) (Student::where('tenant_id', $tenantId)->max('k40_uid') ?? 250);
            $nextUid = max(250, $currentMax) + 1;

            $query = Student::where('tenant_id', $tenantId)
                ->whereNull('k40_uid');

            if ($classFilter && $classFilter !== 'all') {
                $query->where('class_id', (int) $classFilter);
            }

            // Order by class level then name for intuitive ID sequence
            $unassigned = $query->with('schoolClass')
                ->get()
                ->sort(function (Student $a, Student $b) {
                    $classCompare = ($a->schoolClass?->level ?? 999) <=> ($b->schoolClass?->level ?? 999);
                    if ($classCompare !== 0) {
                        return $classCompare;
                    }
                    return strcmp((string) $a->full_name, (string) $b->full_name);
                });

            $count = 0;
            foreach ($unassigned as $student) {
                // Ensure no collision
                while (Student::where('tenant_id', $tenantId)->where('k40_uid', $nextUid)->exists()) {
                    $nextUid++;
                }

                $student->k40_uid = $nextUid++;
                $student->save();
                $count++;
            }

            return $count;
        });
    }
}
