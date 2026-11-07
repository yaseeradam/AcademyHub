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
@endphp

{{-- ==================== DOSSIER PAGE 1: THE CUMULATIVE ACADEMIC LEDGER ==================== --}}
<div class="dossier-page page-break">
    <div class="dossier-outer-frame">
        <div class="dossier-inner-frame">
            {{-- Header --}}
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            <div class="logo-box">
                                @if ($base64Logo)
                                    <img src="{{ $base64Logo }}" class="logo" alt="Logo" />
                                @else
                                    <div style="font-size:18px; font-weight:800; color:#1e3a5f; line-height:46px; text-align:center;">{{ strtoupper(substr($schoolName, 0, 1)) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="header-cell">
                            <div class="school-name">{{ $schoolName }}</div>
                            @if ($motto)
                                <div class="school-motto">{{ $motto }}</div>
                            @endif
                            <div class="school-info">{{ $address }}@if($phone) &bull; Tel: {{ $phone }}@endif</div>
                            <div class="report-badge">ANNUAL ACADEMIC DOSSIER &bull; CUMULATIVE LEDGER</div>
                        </td>
                        <td class="logo-cell" style="text-align:right;">
                            @if (!empty($qrCodeUri) && ($rcOptions['show_qr_code'] ?? true))
                                <img src="{{ $qrCodeUri }}" style="width:48px; height:48px; display:inline-block;" alt="QR" />
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Meta Bar --}}
            <div class="meta-bar">
                <div class="meta-cell">
                    <span class="meta-label">Pupil Legal Name</span>
                    <span class="meta-value">{{ $studentName }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Admission Number</span>
                    <span class="meta-value">{{ $student->admission_number ?? 'N/A' }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Class & Session</span>
                    <span class="meta-value">{{ $classFull }} &bull; {{ $session }}</span>
                </div>
            </div>

            {{-- Full-Year Cumulative Performance Ledger Table --}}
            <table class="scores ledger-table">
                <thead>
                    <tr>
                        <th class="subj-th" rowspan="2" style="width:26%; text-align:left; padding-left:8px;">Curriculum Subject</th>
                        <th colspan="2" style="width:14%;">First Term</th>
                        <th colspan="2" style="width:14%;">Second Term</th>
                        <th colspan="2" style="width:14%;">Third Term</th>
                        <th colspan="4" style="background:#0f2744; width:32%;">Cumulative Session Summary</th>
                    </tr>
                    <tr>
                        {{-- Term 1 --}}
                        <th style="width:8%;">Score</th>
                        <th style="width:6%;">Grd</th>
                        {{-- Term 2 --}}
                        <th style="width:8%;">Score</th>
                        <th style="width:6%;">Grd</th>
                        {{-- Term 3 --}}
                        <th style="width:8%;">Score</th>
                        <th style="width:6%;">Grd</th>
                        {{-- Annual --}}
                        <th style="width:8%; background:#163252;">Total</th>
                        <th style="width:9%; background:#163252;">Session Avg</th>
                        <th style="width:7%; background:#163252;">Grd</th>
                        <th style="width:8%; background:#163252;">Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjectRows as $row)
                    @php
                        $t1 = $row['terms'][1];
                        $t2 = $row['terms'][2];
                        $t3 = $row['terms'][3];
                    @endphp
                    <tr>
                        <td class="subj">{{ $row['subject']?->name ?? 'Subject' }}</td>
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
                        <td class="gold-cell bold">{{ $row['annual_total'] !== null ? $row['annual_total'] : '—' }}</td>
                        <td class="gold-cell bold">{{ $row['annual_average'] !== null ? number_format($row['annual_average'], 1).'%' : '—' }}</td>
                        <td class="bold" style="color:#1e3a5f;">{{ $row['annual_grade'] ?? '—' }}</td>
                        <td class="bold">{{ $row['annual_rank'] ? $row['annual_rank'] : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Term-by-Term Grand Summary Table --}}
            <table class="scores" style="margin-top:6px; margin-bottom:6px;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding-left:8px; width:26%;">Assessment Session</th>
                        <th style="width:18%;">Grand Aggregate</th>
                        <th style="width:18%;">Terminal Average</th>
                        <th style="width:18%;">Terminal Position</th>
                        <th style="width:20%;">Class Average</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([1 => 'First Term Examination', 2 => 'Second Term Examination', 3 => 'Third Term Examination'] as $tNum => $tLabel)
                    @php $ts = $termSummaries[$tNum] ?? []; @endphp
                    <tr>
                        <td class="subj" style="font-weight:700;">{{ $tLabel }}</td>
                        <td class="bold">{{ $ts['grand_total'] !== null ? $ts['grand_total'] : '—' }}</td>
                        <td class="gold-cell bold">{{ $ts['average'] !== null ? number_format($ts['average'], 2).'%' : '—' }}</td>
                        <td class="bold">{{ $ts['position'] ? $ts['position'].' of '.$totalStudents : '—' }}</td>
                        <td>{{ $ts['class_average'] !== null ? number_format($ts['class_average'], 2).'%' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Stats Summary Bar --}}
            <div class="stats-bar" style="margin-top:6px;">
                <div class="stat">
                    <div class="stat-inner">
                        <div class="stat-label">Session Grand Total</div>
                        <div class="stat-value">{{ $annualGrandTotal }}</div>
                    </div>
                </div>
                <div class="stat">
                    <div class="stat-inner gold">
                        <div class="stat-label">Cumulative Session Average</div>
                        <div class="stat-value">{{ number_format($cumulativeAnnualAverage, 2) }}%</div>
                    </div>
                </div>
                <div class="stat">
                    <div class="stat-inner gold">
                        <div class="stat-label">Annual Class Rank</div>
                        <div class="stat-value">
                            @if ($annualPosition)
                                {{ $annualPosition }}<span style="font-size:9px; color:#64748b;"> / {{ $totalStudents }}</span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
                <div class="stat">
                    <div class="stat-inner">
                        <div class="stat-label">Class Session Benchmark</div>
                        <div class="stat-value">{{ number_format($annualClassAverage, 2) }}%</div>
                    </div>
                </div>
            </div>

            <div class="footer-note" style="margin-top:6px; text-align:center; font-size:7px; color:#64748b;">
                Cumulative Academic Ledger &bull; {{ $schoolName }} &bull; Issued for {{ $session }} Session
            </div>
        </div>
    </div>
</div>

{{-- ==================== DOSSIER PAGE 2: VERDICT, ATTENDANCE & TRAITS ==================== --}}
<div class="dossier-page">
    <div class="dossier-outer-frame">
        <div class="dossier-inner-frame">
            {{-- Header Strip --}}
            <div class="page2-header-strip">
                <span class="strip-title">ACADEMIC PROGRESSION VERDICT &amp; CHARACTER PORTFOLIO</span>
                <span class="strip-info">{{ $studentName }} &bull; {{ $student->admission_number }}</span>
            </div>

            {{-- Promotion Verdict Decree --}}
            <div class="verdict-decree">
                <div class="verdict-decree-header">OFFICIAL ACADEMIC BOARD PROMOTION VERDICT</div>
                <div class="verdict-decree-body">
                    <div class="verdict-decree-title">{{ $promotion['message'] ?? 'ACADEMIC ASSESSMENT COMPLETED' }}</div>
                    <div class="verdict-decree-sub">
                        @if (!empty($promotion['is_final']))
                            Faculty promotion decision ratified on the basis of {{ number_format($cumulativeAnnualAverage, 1) }}% cumulative session average across all evaluated subjects.
                        @else
                            Academic Session In Progress &bull; Continuous assessment portfolio compiled up to active term.
                        @endif
                    </div>
                </div>
            </div>

            {{-- Two Column Table: Attendance & Psychomotor --}}
            <div class="two-col-table" style="margin-top:6px;">
                {{-- Left: Attendance Timeline & Grading Scale --}}
                <div class="col-left">
                    <div class="sub-card">
                        <div class="sub-card-header">LONGITUDINAL ATTENDANCE REGISTER</div>
                        <table class="scores" style="margin:0; border:none;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding-left:6px;">Assessment Period</th>
                                    <th>Days Opened</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([1 => 'First Term', 2 => 'Second Term', 3 => 'Third Term'] as $tNum => $tName)
                                @php $attT = $attendanceTimeline['terms'][$tNum] ?? []; @endphp
                                <tr>
                                    <td class="subj" style="font-size:7.5px;">{{ $tName }}</td>
                                    <td>{{ $attT['opened'] ?? '—' }}</td>
                                    <td class="bold" style="color:#15803d;">{{ $attT['present'] ?? '—' }}</td>
                                    <td style="color:#dc2626;">{{ $attT['absent'] ?? '—' }}</td>
                                    <td class="bold">{{ $attT['rate'] !== null ? $attT['rate'].'%' : '—' }}</td>
                                </tr>
                                @endforeach
                                <tr style="background:#fef3c7; font-weight:800;">
                                    <td class="subj" style="font-size:7.5px;">SESSION TOTAL</td>
                                    <td>{{ $attendanceTimeline['total_opened'] ?? '—' }}</td>
                                    <td style="color:#15803d;">{{ $attendanceTimeline['total_present'] ?? '—' }}</td>
                                    <td style="color:#dc2626;">{{ $attendanceTimeline['total_absent'] ?? '—' }}</td>
                                    <td style="color:#1e3a5f;">{{ $attendanceTimeline['annual_rate'] !== null ? $attendanceTimeline['annual_rate'].'%' : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="sub-card" style="margin-top:6px;">
                        <div class="sub-card-header">CRITERIA &amp; GRADING INTERPRETATION</div>
                        <table class="scores" style="margin:0; border:none;">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Score Range</th>
                                    <th style="text-align:left; padding-left:6px;">Academic Standing</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="bold" style="color:#15803d;">A</td><td>70% &ndash; 100%</td><td style="text-align:left; padding-left:6px;">Distinction / Excellent</td></tr>
                                <tr><td class="bold" style="color:#0369a1;">B</td><td>60% &ndash; 69%</td><td style="text-align:left; padding-left:6px;">Credit / Very Good</td></tr>
                                <tr><td class="bold" style="color:#c4975a;">C</td><td>50% &ndash; 59%</td><td style="text-align:left; padding-left:6px;">Merit / Good</td></tr>
                                <tr><td class="bold" style="color:#c2410c;">D</td><td>40% &ndash; 49%</td><td style="text-align:left; padding-left:6px;">Pass / Fair</td></tr>
                                <tr><td class="bold" style="color:#dc2626;">F</td><td>0% &ndash; 39%</td><td style="text-align:left; padding-left:6px;">Unsatisfactory</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Right: Affective & Psychomotor Traits --}}
                @php
                    $affectiveRows = !empty($affectiveTimeline) ? $affectiveTimeline : array_filter($psychomotorTimeline, fn($t) => ($t['domain'] ?? 'affective') === 'affective');
                    $psychomotorRows = !empty($psychomotorDomainTimeline) ? $psychomotorDomainTimeline : array_filter($psychomotorTimeline, fn($t) => ($t['domain'] ?? '') === 'psychomotor');
                    if (empty($psychomotorRows)) {
                        $half = ceil(count($psychomotorTimeline) / 2);
                        $affectiveRows = array_slice($psychomotorTimeline, 0, $half);
                        $psychomotorRows = array_slice($psychomotorTimeline, $half);
                    }
                @endphp
                <div class="col-right">
                    <div class="sub-card">
                        <div class="sub-card-header">AFFECTIVE &amp; BEHAVIOURAL DOMAIN</div>
                        <table class="scores" style="margin:0; border:none;">
                            <thead>
                                <tr>
                                    <th style="width:58%; text-align:left; padding-left:6px;">Character Dimension</th>
                                    <th style="width:14%;">Term 1</th>
                                    <th style="width:14%;">Term 2</th>
                                    <th style="width:14%;">Term 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($affectiveRows as $trait)
                                <tr>
                                    <td class="subj" style="font-size:7px; padding:1.5px 4px;">{{ $trait['label'] }}</td>
                                    <td class="bold" style="font-size:7px; padding:1.5px 2px;">{{ $trait['term_1'] }}</td>
                                    <td class="bold" style="font-size:7px; padding:1.5px 2px;">{{ $trait['term_2'] }}</td>
                                    <td class="bold" style="font-size:7px; padding:1.5px 2px;">{{ $trait['term_3'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="sub-card" style="margin-top:5px;">
                        <div class="sub-card-header">PSYCHOMOTOR &amp; PRACTICAL SKILLS</div>
                        <table class="scores" style="margin:0; border:none;">
                            <thead>
                                <tr>
                                    <th style="width:58%; text-align:left; padding-left:6px;">Practical Skill</th>
                                    <th style="width:14%;">Term 1</th>
                                    <th style="width:14%;">Term 2</th>
                                    <th style="width:14%;">Term 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($psychomotorRows as $trait)
                                <tr>
                                    <td class="subj" style="font-size:7px; padding:1.5px 4px;">{{ $trait['label'] }}</td>
                                    <td class="bold" style="font-size:7px; padding:1.5px 2px;">{{ $trait['term_1'] }}</td>
                                    <td class="bold" style="font-size:7px; padding:1.5px 2px;">{{ $trait['term_2'] }}</td>
                                    <td class="bold" style="font-size:7px; padding:1.5px 2px;">{{ $trait['term_3'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Observations --}}
            <div class="sub-card" style="margin-top:6px;">
                <div class="sub-card-header">EXECUTIVE OBSERVATIONS &amp; ENDORSEMENTS</div>
                <div style="padding:6px 8px;">
                    <div style="margin-bottom:4px;">
                        <span style="font-size:7px; font-weight:800; color:#c4975a; text-transform:uppercase;">Class Tutor Annual Summary:</span>
                        <div style="font-size:8px; font-style:italic; color:#1e293b; margin-top:1px;">"{{ $teacherRemarks }}"</div>
                    </div>
                    <div>
                        <span style="font-size:7px; font-weight:800; color:#1e3a5f; text-transform:uppercase;">Principal / Head of Institution Verdict:</span>
                        <div style="font-size:8px; font-style:italic; color:#1e293b; margin-top:1px;">"{{ $principalRemarks }}"</div>
                    </div>
                </div>
            </div>

            {{-- Resumption Bar --}}
            @if ($nextTermDate && ($rcOptions['show_next_term_date'] ?? true))
            <div class="resumption-stripe">
                Next Academic Session Resumes: <strong>{{ $nextTermDate }}</strong>
            </div>
            @endif

            {{-- Official Endorsement Signatures --}}
            @if ($rcOptions['show_signatures'] ?? true)
            <div class="sigs-table" style="margin-top:8px;">
                <div class="sig-cell">
                    @if (!empty($signatureImages['teacher']) && file_exists($signatureImages['teacher']))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureImages['teacher'])) }}" class="sig-img" alt="Teacher Sig" />
                        <div class="sig-line has-img">Form Tutor's Signature</div>
                    @else
                        <div class="sig-line">Form Tutor's Signature</div>
                    @endif
                    <div class="sig-sub">Academic Faculty</div>
                </div>

                <div class="sig-cell">
                    @if (!empty($qrCodeUri) && ($rcOptions['show_qr_code'] ?? true))
                        <img src="{{ $qrCodeUri }}" style="width:40px; height:40px; margin:0 auto;" alt="Seal" />
                        <div class="sig-sub" style="font-weight:800; color:#1e3a5f; margin-top:1px;">DIGITAL VERIFICATION SEAL</div>
                    @endif
                </div>

                <div class="sig-cell">
                    @if (!empty($signatureImages['principal']) && file_exists($signatureImages['principal']))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureImages['principal'])) }}" class="sig-img" alt="Principal Sig" />
                        <div class="sig-line has-img">{{ $principalName }}</div>
                    @else
                        <div class="sig-line">{{ $principalName }}</div>
                    @endif
                    <div class="sig-sub">{{ $principalTitle }} &bull; Head of Institution</div>
                </div>
            </div>
            @endif

            <div class="footer-note" style="margin-top:6px; text-align:center; font-size:7px; color:#64748b;">
                Page 2 of Annual Dossier &bull; {{ $schoolName }} &bull; Certified Official Document
            </div>
        </div>
    </div>
</div>
