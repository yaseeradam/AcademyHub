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

<div class="book-cover-page">
    <div class="cover-outer-frame">
        <div class="cover-inner-frame">
            {{-- Institutional Masthead --}}
            <div class="cover-header">
                <div class="cover-logo-wrapper">
                    @if ($base64Logo)
                        <div class="cover-logo-box">
                            <img src="{{ $base64Logo }}" class="cover-logo-img" alt="School Crest" />
                        </div>
                    @else
                        <div class="cover-logo-box placeholder-crest">
                            <span>{{ strtoupper(substr($schoolName, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>

                <div class="cover-school-name">{{ $schoolName }}</div>
                @if ($motto)
                    <div class="cover-school-motto">{{ $motto }}</div>
                @endif
                <div class="cover-school-meta">
                    {{ $address }}@if($phone) &bull; Tel: {{ $phone }}@endif
                </div>
            </div>

            {{-- Classical Divider --}}
            <div class="cover-fillet">
                <div class="fillet-line"></div>
                <div class="fillet-tag">OFFICIAL ACADEMIC ARCHIVE</div>
                <div class="fillet-line"></div>
            </div>

            {{-- Document Title --}}
            <div class="cover-title-area">
                <div class="cover-doc-type">CONTINUOUS ASSESSMENT & TERMINAL RECORDS</div>
                <div class="cover-doc-title">STUDENT REPORT BOOKLET</div>
                <div class="cover-session-badge">
                    {{ $session }} ACADEMIC SESSION
                </div>
            </div>

            {{-- Student Credential Plaque --}}
            <div class="cover-student-card">
                <table class="cover-student-table">
                    <tr>
                        <td class="cover-photo-cell">
                            <div class="cover-photo-frame">
                                @if ($base64StudentPhoto)
                                    <img src="{{ $base64StudentPhoto }}" class="cover-photo-img" alt="{{ $studentName }}" />
                                @else
                                    <div class="cover-photo-placeholder">
                                        <div class="placeholder-initial">{{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}</div>
                                        <div class="placeholder-label">STUDENT</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="cover-meta-cell">
                            <div class="cover-name-banner">
                                <div class="cover-student-name">{{ $studentName }}</div>
                                <div class="cover-student-class">{{ $classFull }}</div>
                            </div>

                            <table class="cover-data-grid">
                                <tr>
                                    <td class="data-key">Admission Number</td>
                                    <td class="data-val"><strong>{{ $student->admission_number ?? 'N/A' }}</strong></td>
                                    <td class="data-key">Gender</td>
                                    <td class="data-val">{{ ucfirst($student->gender ?? '—') }}</td>
                                </tr>
                                <tr>
                                    <td class="data-key">Date of Birth</td>
                                    <td class="data-val">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d F, Y') : '—' }}</td>
                                    <td class="data-key">Blood Group</td>
                                    <td class="data-val">{{ $student->blood_group ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="data-key">Enrolment Status</td>
                                    <td class="data-val">Active Scholar</td>
                                    <td class="data-key">Record ID</td>
                                    <td class="data-val">#{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Folio Index --}}
            <div class="cover-folio-section">
                <div class="folio-heading">CONTENTS OF THIS RECORD BOOK</div>
                <table class="folio-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">FOLIO SECTION</th>
                            <th style="width: 55%;">DESCRIPTION & COVERAGE</th>
                            <th style="width: 20%; text-align: center;">RECORD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="folio-num">FOLIO I</td>
                            <td class="folio-desc">First Term Continuous Assessment, Tests & Terminal Examination</td>
                            <td class="folio-tag">Enclosed</td>
                        </tr>
                        <tr>
                            <td class="folio-num">FOLIO II</td>
                            <td class="folio-desc">Second Term Continuous Assessment, Tests & Terminal Examination</td>
                            <td class="folio-tag">Enclosed</td>
                        </tr>
                        <tr>
                            <td class="folio-num">FOLIO III</td>
                            <td class="folio-desc">Third Term Continuous Assessment, Tests & Promotional Examination</td>
                            <td class="folio-tag">Enclosed</td>
                        </tr>
                        <tr>
                            <td class="folio-num">ANNUAL DOSSIER</td>
                            <td class="folio-desc">Cumulative Three-Term Ledger, Attendance Portfolio & Promotion Verdict</td>
                            <td class="folio-tag">Enclosed</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Institutional Endorsement & Seal --}}
            <div class="cover-endorsement-section">
                <table class="cover-endorsement-table">
                    <tr>
                        <td class="endorse-left">
                            <div class="endorse-heading">OFFICIAL CERTIFICATION</div>
                            <div class="endorse-text">
                                This volume constitutes the continuous assessment record of {{ $schoolName }}. Certified valid only when bearing the official institutional watermark and executive signature.
                            </div>
                            <div class="endorse-date">
                                Date of Issue: <strong>{{ now()->format('d F, Y') }}</strong>
                            </div>
                        </td>

                        <td class="endorse-center">
                            @if (!empty($qrCodeUri) && ($rcOptions['show_qr_code'] ?? true))
                                <div class="endorse-qr-box">
                                    <img src="{{ $qrCodeUri }}" class="endorse-qr-img" alt="Digital Seal" />
                                </div>
                                <div class="endorse-qr-text">DIGITAL RECORD SEAL</div>
                            @endif
                        </td>

                        <td class="endorse-right">
                            @if (!empty($signatureImages['principal']) && file_exists($signatureImages['principal']))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureImages['principal'])) }}" class="endorse-sig-img" alt="Principal Signature" />
                                <div class="endorse-sig-line has-img">{{ $principalName }}</div>
                            @else
                                <div class="endorse-sig-spacer"></div>
                                <div class="endorse-sig-line">{{ $principalName }}</div>
                            @endif
                            <div class="endorse-sig-title">{{ $principalTitle }} &bull; Head of Institution</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
