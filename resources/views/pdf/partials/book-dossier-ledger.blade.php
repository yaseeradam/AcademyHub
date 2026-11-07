@php
    $schoolName = $schoolName ?? config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = $schoolLogo ?? config('academyhub.school_logo');
    $logoPath = $logo ? (file_exists($logo) ? $logo : public_path('uploads/'.str_replace('\\', '/', $logo))) : null;

    $base64Logo = null;
    if ($logoPath && file_exists($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    $base64StudentPhoto = $student->passport_photo_data_uri;

    $address = $schoolAddress ?? config('academyhub.school_address', '');
    $phone = $schoolPhone ?? config('academyhub.school_phone', '');
    $motto = $schoolMotto ?? config('academyhub.school_motto', 'Excellence and Integrity in Education');

    $studentName = trim("{$student->first_name} {$student->middle_name} {$student->last_name}");
    $className = $student->schoolClass?->name ?? 'Class';
    $sectionName = $student->section?->name ? " - {$student->section->name}" : '';
    $classFull = "{$className}{$sectionName}";

    $verdictClass = match($promotion['badge'] ?? 'progress') {
        'success' => 'verdict-success',
        'warning' => 'verdict-warning',
        'danger'  => 'verdict-danger',
        default   => 'verdict-progress',
    };
@endphp

{{-- ==================== DOSSIER PAGE 1: THE ACADEMIC LEDGER ==================== --}}
<div class="dossier-page page-break">
    {{-- Header Banner --}}
    <div class="school-header">
        <div class="logo-cell">
            @if ($base64Logo)
                <img src="{{ $base64Logo }}" class="logo-img" alt="School Logo" />
            @else
                <div class="logo-placeholder">{{ strtoupper(substr($schoolName, 0, 1)) }}</div>
            @endif
        </div>
        <div class="title-cell">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-motto">{{ $motto }}</div>
            <div class="school-address">{{ $address }} {{ $phone ? ' • Tel: '.$phone : '' }}</div>
        </div>
        <div class="qr-header-cell">
            @if (!empty($qrCodeUri) && ($rcOptions['show_qr_code'] ?? true))
                <img src="{{ $qrCodeUri }}" class="qr-header-img" alt="Verify QR" />
            @endif
        </div>
    </div>

    {{-- Title Ribbon --}}
    <div class="booklet-ribbon">
        Annual Academic Dossier • Continuous Assessment Report Booklet
    </div>

    {{-- Student Profile Grid --}}
    <div class="student-profile">
        <div class="photo-cell">
            @if ($base64StudentPhoto)
                <img src="{{ $base64StudentPhoto }}" class="student-photo" alt="Photo" />
            @else
                <div class="student-photo-placeholder">{{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}</div>
            @endif
        </div>
        <div class="bio-cell">
            <table class="bio-table">
                <tr>
                    <td class="bio-label">Pupil Name:</td>
                    <td class="bio-val">{{ $studentName }}</td>
                    <td class="bio-label">Admission No:</td>
                    <td class="bio-val">{{ $student->admission_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="bio-label">Class / Section:</td>
                    <td class="bio-val">{{ $classFull }}</td>
                    <td class="bio-label">Academic Year:</td>
                    <td class="bio-val">{{ $session }}</td>
                </tr>
                <tr>
                    <td class="bio-label">Gender:</td>
                    <td class="bio-val">{{ $student->gender ?? '—' }}</td>
                    <td class="bio-label">Class Population:</td>
                    <td class="bio-val">{{ $totalStudents }} Pupils</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Full-Year Comparative Academic Performance Matrix --}}
    <table class="ledger-table">
        <thead>
            <tr>
                <th class="subj-th" rowspan="2">Subject Curriculum</th>
                <th colspan="2">1st Term</th>
                <th colspan="2">2nd Term</th>
                <th colspan="2">3rd Term</th>
                <th colspan="4" style="background:#e2e8f0; color:#1e3a8a;">Cumulative Annual Record</th>
                <th rowspan="2" style="width:18%;">Subject Teacher Remarks</th>
            </tr>
            <tr>
                {{-- Term 1 --}}
                <th style="width:7%;">Score</th>
                <th style="width:5%;">Grd</th>
                {{-- Term 2 --}}
                <th style="width:7%;">Score</th>
                <th style="width:5%;">Grd</th>
                {{-- Term 3 --}}
                <th style="width:7%;">Score</th>
                <th style="width:5%;">Grd</th>
                {{-- Annual --}}
                <th style="width:7%;">Total</th>
                <th style="width:7%;">Avg%</th>
                <th style="width:5%;">Grd</th>
                <th style="width:5%;">Pos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subjectRows as $row)
            @php
                $t1 = $row['terms'][1];
                $t2 = $row['terms'][2];
                $t3 = $row['terms'][3];
                
                $gBadgeClass = match(strtoupper((string) ($row['annual_grade'] ?? ''))) {
                    'A' => 'badge-a',
                    'B' => 'badge-b',
                    'C' => 'badge-c',
                    'D' => 'badge-d',
                    default => 'badge-f',
                };
            @endphp
            <tr>
                <td class="subj-td">{{ $row['subject']?->name ?? 'Subject' }}</td>
                {{-- Term 1 --}}
                <td class="bold">{{ $t1['total'] !== null ? $t1['total'] : '—' }}</td>
                <td>{{ $t1['grade'] ?? '—' }}</td>
                {{-- Term 2 --}}
                <td class="bold">{{ $t2['total'] !== null ? $t2['total'] : ($t2['is_pending'] ? '—' : '0') }}</td>
                <td>{{ $t2['grade'] ?? ($t2['is_pending'] ? '—' : '—') }}</td>
                {{-- Term 3 --}}
                <td class="bold">{{ $t3['total'] !== null ? $t3['total'] : ($t3['is_pending'] ? '—' : '0') }}</td>
                <td>{{ $t3['grade'] ?? ($t3['is_pending'] ? '—' : '—') }}</td>
                {{-- Cumulative Annual --}}
                <td class="highlight">{{ $row['annual_total'] !== null ? $row['annual_total'] : '—' }}</td>
                <td class="highlight">{{ $row['annual_average'] !== null ? number_format($row['annual_average'], 1).'%' : '—' }}</td>
                <td>
                    @if ($row['annual_grade'])
                        <span class="grade-badge {{ $gBadgeClass }}">{{ $row['annual_grade'] }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="bold">{{ $row['annual_rank'] ? $row['annual_rank'] : '—' }}</td>
                {{-- Subject Teacher Remarks --}}
                <td style="font-size:6.5pt; text-align:left; padding-left:4px; font-style:italic; color:#475569;">
                    {{ $t3['remarks'] ?: ($t2['remarks'] ?: ($t1['remarks'] ?: ($row['annual_average'] >= 70 ? 'Excellent mastery' : ($row['annual_average'] >= 50 ? 'Good credit pass' : 'Requires revision')))) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Term-by-Term Subtotal Summary Table --}}
    <table class="ledger-table" style="margin-bottom: 12px;">
        <thead>
            <tr>
                <th style="width: 25%;">Terminal Assessment Period</th>
                <th style="width: 20%;">Grand Total</th>
                <th style="width: 20%;">Term Average (%)</th>
                <th style="width: 15%;">Class Position</th>
                <th style="width: 20%;">Class Average (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach([1 => '1st Term', 2 => '2nd Term', 3 => '3rd Term'] as $tNum => $tLabel)
            @php $ts = $termSummaries[$tNum] ?? []; @endphp
            <tr>
                <td class="bold" style="text-align:left; padding-left:8px;">{{ $tLabel }}</td>
                <td>{{ $ts['grand_total'] !== null ? $ts['grand_total'] : '—' }}</td>
                <td class="highlight">{{ $ts['average'] !== null ? number_format($ts['average'], 2).'%' : '—' }}</td>
                <td class="bold">{{ $ts['position'] ? $ts['position'].' of '.$totalStudents : '—' }}</td>
                <td>{{ $ts['class_average'] !== null ? number_format($ts['class_average'], 2).'%' : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Session KPI Summary Cards --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Annual Grand Total</div>
            <div class="kpi-value">{{ $annualGrandTotal }}</div>
            <div class="kpi-sub">Across All Subjects</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cumulative Session Average</div>
            <div class="kpi-value" style="color:#0284c7;">{{ number_format($cumulativeAnnualAverage, 2) }}%</div>
            <div class="kpi-sub">Overall Academic Standing</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Annual Class Rank</div>
            <div class="kpi-value" style="color:#16a34a;">
                @if ($annualPosition)
                    {{ $annualPosition }}<span style="font-size:8pt;">/{{ $totalStudents }}</span>
                @else
                    —
                @endif
            </div>
            <div class="kpi-sub">Class Position</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Class Annual Average</div>
            <div class="kpi-value" style="color:#64748b;">{{ number_format($annualClassAverage, 2) }}%</div>
            <div class="kpi-sub">Benchmark Average</div>
        </div>
    </div>

    <div class="footer">
        Continuous Assessment Ledger • {{ $schoolName }} • Verified & Authenticated Digital Academic Record
    </div>
</div>


{{-- ==================== DOSSIER PAGE 2: DEVELOPMENT, VERDICT & OFFICIAL ENDORSEMENT ==================== --}}
<div class="dossier-page">
    {{-- Page 2 Header Strip --}}
    <div class="page2-header">
        <div class="page2-header-title">Student Annual Dossier • Character & Progress Portfolio</div>
        <div class="page2-header-info">Pupil: <strong>{{ $studentName }}</strong> ({{ $student->admission_number }}) • Session: {{ $session }}</div>
    </div>

    {{-- Promotion Verdict Banner --}}
    <div class="verdict-box {{ $verdictClass }}">
        <div class="verdict-title">{{ $promotion['message'] ?? 'ACADEMIC ASSESSMENT COMPLETED' }}</div>
        <div class="verdict-sub">
            @if (!empty($promotion['is_final']))
                Official Annual Faculty Verdict based on {{ number_format($cumulativeAnnualAverage, 1) }}% Cumulative Academic Average
            @else
                Session In Progress • Continuous Assessment Evaluation Record
            @endif
        </div>
    </div>

    {{-- Two Column Grid: Attendance & Affective Development --}}
    <div class="two-col-table">
        {{-- Left: Longitudinal Attendance Timeline --}}
        <div class="col-left">
            <div class="section-box">
                <div class="section-header">Longitudinal Attendance Register</div>
                <div class="section-body">
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Days Opened</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Rate (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([1 => '1st Term', 2 => '2nd Term', 3 => '3rd Term'] as $tNum => $tName)
                            @php $attT = $attendanceTimeline['terms'][$tNum] ?? []; @endphp
                            <tr>
                                <td class="label-td">{{ $tName }}</td>
                                <td>{{ $attT['opened'] ?? '—' }}</td>
                                <td style="color:#16a34a; font-weight:700;">{{ $attT['present'] ?? '—' }}</td>
                                <td style="color:#dc2626;">{{ $attT['absent'] ?? '—' }}</td>
                                <td style="font-weight:700;">{{ $attT['rate'] !== null ? $attT['rate'].'%' : '—' }}</td>
                            </tr>
                            @endforeach
                            <tr style="background:#f1f5f9; font-weight:800;">
                                <td class="label-td">ANNUAL TOTAL</td>
                                <td>{{ $attendanceTimeline['total_opened'] ?? '—' }}</td>
                                <td style="color:#16a34a;">{{ $attendanceTimeline['total_present'] ?? '—' }}</td>
                                <td style="color:#dc2626;">{{ $attendanceTimeline['total_absent'] ?? '—' }}</td>
                                <td style="color:#1e3a8a;">{{ $attendanceTimeline['annual_rate'] !== null ? $attendanceTimeline['annual_rate'].'%' : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Grading System Interpretation Box --}}
            <div class="section-box">
                <div class="section-header">Grading Scale & Performance Criteria</div>
                <div class="section-body" style="padding:4px 6px;">
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Range</th>
                                <th>Interpretation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td style="font-weight:800; color:#15803d;">A</td><td>70% – 100%</td><td style="text-align:left; padding-left:4px;">Excellent / Distinction</td></tr>
                            <tr><td style="font-weight:800; color:#0369a1;">B</td><td>60% – 69%</td><td style="text-align:left; padding-left:4px;">Very Good / Credit</td></tr>
                            <tr><td style="font-weight:800; color:#a16207;">C</td><td>50% – 59%</td><td style="text-align:left; padding-left:4px;">Good / Satisfactory</td></tr>
                            <tr><td style="font-weight:800; color:#c2410c;">D</td><td>40% – 49%</td><td style="text-align:left; padding-left:4px;">Fair / Pass</td></tr>
                            <tr><td style="font-weight:800; color:#b91c1c;">F</td><td>0% – 39%</td><td style="text-align:left; padding-left:4px;">Unsatisfactory / Fail</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Psychomotor & Affective Development Matrix --}}
        <div class="col-right">
            <div class="section-box">
                <div class="section-header">Affective & Behavioral Traits Portfolio</div>
                <div class="section-body">
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th style="width:55%;">Character & Skill Dimension</th>
                                <th style="width:15%;">Term 1</th>
                                <th style="width:15%;">Term 2</th>
                                <th style="width:15%;">Term 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($psychomotorTimeline as $trait)
                            <tr>
                                <td class="label-td" style="font-size:6.5pt;">{{ $trait['label'] }}</td>
                                <td style="font-weight:600; font-size:6.5pt;">{{ $trait['term_1'] }}</td>
                                <td style="font-weight:600; font-size:6.5pt;">{{ $trait['term_2'] }}</td>
                                <td style="font-weight:600; font-size:6.5pt;">{{ $trait['term_3'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Comprehensive Annual Observations & Endorsements --}}
    <div class="section-box">
        <div class="section-header">Official Annual Observations & Administrative Remarks</div>
        <div class="section-body" style="padding:8px 10px;">
            <div class="remark-row">
                <div class="remark-title">Class Teacher's Annual Summary Report:</div>
                <div class="remark-text">"{{ $teacherRemarks }}"</div>
            </div>
            <div class="remark-row" style="margin-top:6px;">
                <div class="remark-title">Principal's / Head of School's Comprehensive Verdict:</div>
                <div class="remark-text">"{{ $principalRemarks }}"</div>
            </div>
        </div>
    </div>

    {{-- Resumption Date & Official Calendar Bar --}}
    @if ($nextTermDate && ($rcOptions['show_next_term_date'] ?? true))
    <div class="resumption-bar">
        Next Academic Session / Term Resumes: <strong>{{ $nextTermDate }}</strong>
    </div>
    @endif

    {{-- Official Signatures & Seal Table --}}
    @if ($rcOptions['show_signatures'] ?? true)
    <div class="sigs-table">
        <div class="sig-cell">
            @if (!empty($signatureImages['teacher']) && file_exists($signatureImages['teacher']))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureImages['teacher'])) }}" class="sig-img" alt="Teacher Sig" />
                <div class="sig-line has-img">Class Teacher's Signature</div>
            @else
                <div class="sig-line">Class Teacher's Signature</div>
            @endif
            <div class="sig-sub">Form Faculty Member</div>
        </div>

        <div class="sig-cell">
            @if (!empty($qrCodeUri) && ($rcOptions['show_qr_code'] ?? true))
                <img src="{{ $qrCodeUri }}" style="width:46px; height:46px; margin:0 auto;" alt="Official Seal" />
                <div class="sig-sub" style="font-weight:700; color:#1e3a8a; margin-top:2px;">OFFICIAL DIGITAL SEAL</div>
            @endif
        </div>

        <div class="sig-cell">
            @if (!empty($signatureImages['principal']) && file_exists($signatureImages['principal']))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureImages['principal'])) }}" class="sig-img" alt="Principal Sig" />
                <div class="sig-line has-img">{{ $principalName }}</div>
            @else
                <div class="sig-line">{{ $principalName }}</div>
            @endif
            <div class="sig-sub">{{ $principalTitle }} / Head of School</div>
        </div>
    </div>
    @endif

    <div class="footer" style="margin-top:12px;">
        Official Continuous Assessment Dossier • Issued by {{ $schoolName }} • Generated on {{ now()->format('d M, Y') }}
    </div>
</div>
