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

    $termsEnclosed = $termsEnclosed ?? [1];
@endphp

<div class="book-cover-page">
    <div class="cover-outer-frame">
        <div class="cover-inner-frame">
            {{-- Corner Ornaments --}}
            <div class="corner-ornament corner-top-left"></div>
            <div class="corner-ornament corner-top-right"></div>
            <div class="corner-ornament corner-bottom-left"></div>
            <div class="corner-ornament corner-bottom-right"></div>

            {{-- School Header --}}
            <div class="cover-header">
                <div class="cover-logo-container">
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
                <div class="cover-school-motto">"{{ $motto }}"</div>
                <div class="cover-school-address">
                    {{ $address }}@if($phone) • Tel: {{ $phone }}@endif
                </div>
            </div>

            {{-- Academic Divider Ribbon --}}
            <div class="cover-divider-wrapper">
                <div class="cover-divider-line"></div>
                <div class="cover-divider-badge">✦ OFFICIAL ACADEMIC DOSSIER ✦</div>
                <div class="cover-divider-line"></div>
            </div>

            {{-- Document Title Ribbon --}}
            <div class="cover-title-section">
                <div class="cover-main-title">OFFICIAL STUDENT REPORT BOOK</div>
                <div class="cover-subtitle">Continuous Assessment Portfolio & Cumulative Terminal Records</div>
                <div class="cover-session-pill">
                    {{ $session }} ACADEMIC SESSION
                </div>
            </div>

            {{-- Student Showcase Card --}}
            <div class="cover-student-card">
                <table class="cover-student-table">
                    <tr>
                        <td class="cover-photo-cell">
                            <div class="cover-photo-frame">
                                @if ($base64StudentPhoto)
                                    <img src="{{ $base64StudentPhoto }}" class="cover-photo-img" alt="{{ $studentName }}" />
                                @else
                                    <div class="cover-photo-placeholder">
                                        <div class="placeholder-avatar">{{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}</div>
                                        <div class="placeholder-sub">STUDENT</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="cover-meta-cell">
                            <div class="cover-student-name">{{ $studentName }}</div>
                            <div class="cover-student-badge-row">
                                <span class="cover-status-tag">OFFICIAL REGISTERED PUPIL</span>
                                <span class="cover-class-tag">{{ $classFull }}</span>
                            </div>

                            <table class="cover-bio-grid">
                                <tr>
                                    <td class="bio-k">Admission Number:</td>
                                    <td class="bio-v"><strong>{{ $student->admission_number ?? 'N/A' }}</strong></td>
                                    <td class="bio-k">Gender:</td>
                                    <td class="bio-v"><strong>{{ ucfirst($student->gender ?? '—') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="bio-k">Class & Arm:</td>
                                    <td class="bio-v"><strong>{{ $classFull }}</strong></td>
                                    <td class="bio-k">Date of Birth:</td>
                                    <td class="bio-v"><strong>{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M, Y') : '—' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="bio-k">Student Registry ID:</td>
                                    <td class="bio-v">#{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="bio-k">Blood Group:</td>
                                    <td class="bio-v">{{ $student->blood_group ?: '—' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Terminal Index / Table of Contents --}}
            <div class="cover-registry-section">
                <div class="cover-registry-heading">COMPREHENSIVE TERMINAL RECORDS REGISTRY</div>
                <table class="cover-registry-table">
                    <thead>
                        <tr>
                            <th style="width: 26%;">RECORD TITLE</th>
                            <th style="width: 52%;">SCOPE & DESCRIPTION</th>
                            <th style="width: 22%;">RECORD STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="reg-title">FIRST TERM REPORT</td>
                            <td class="reg-desc">Continuous Assessment Tests, Terminal Examination, Affective Traits & Attendance</td>
                            <td class="reg-status">
                                <span class="status-pill {{ in_array(1, $termsEnclosed) ? 'status-enclosed' : 'status-pending' }}">
                                    {{ in_array(1, $termsEnclosed) ? 'ENCLOSED IN BOOK' : 'NOT RECORDED' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="reg-title">SECOND TERM REPORT</td>
                            <td class="reg-desc">Continuous Assessment Tests, Terminal Examination, Affective Traits & Attendance</td>
                            <td class="reg-status">
                                <span class="status-pill {{ in_array(2, $termsEnclosed) ? 'status-enclosed' : 'status-pending' }}">
                                    {{ in_array(2, $termsEnclosed) ? 'ENCLOSED IN BOOK' : 'PENDING' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="reg-title">THIRD TERM REPORT</td>
                            <td class="reg-desc">Continuous Assessment Tests, Promotional Examination, Affective Traits & Attendance</td>
                            <td class="reg-status">
                                <span class="status-pill {{ in_array(3, $termsEnclosed) ? 'status-enclosed' : 'status-pending' }}">
                                    {{ in_array(3, $termsEnclosed) ? 'ENCLOSED IN BOOK' : 'PENDING' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="reg-title">ANNUAL ACADEMIC DOSSIER</td>
                            <td class="reg-desc">Cumulative 3-Term Ledger, Longitudinal Attendance, Psychomotor Portfolio & Progression Verdict</td>
                            <td class="reg-status">
                                <span class="status-pill status-enclosed">ENCLOSED IN BOOK</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Institutional Certification & Verification Seal --}}
            <div class="cover-cert-section">
                <table class="cover-cert-table">
                    <tr>
                        <td class="cover-cert-cell cert-left">
                            <div class="cert-label">INSTITUTIONAL CERTIFICATION</div>
                            <div class="cert-text">
                                This official student report booklet is a certified continuous academic record issued under the authority of {{ $schoolName }}. Any alteration or erasure renders this document void.
                            </div>
                            <div class="cert-issue-date">
                                Date of Issuance: <strong>{{ now()->format('d F, Y') }}</strong>
                            </div>
                        </td>

                        <td class="cover-cert-cell cert-center">
                            @if (!empty($qrCodeUri) && ($rcOptions['show_qr_code'] ?? true))
                                <div class="cover-qr-frame">
                                    <img src="{{ $qrCodeUri }}" class="cover-qr-img" alt="Digital Verification" />
                                </div>
                                <div class="cover-qr-caption">SCAN TO VERIFY RECORD</div>
                            @endif
                        </td>

                        <td class="cover-cert-cell cert-right">
                            @if (!empty($signatureImages['principal']) && file_exists($signatureImages['principal']))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signatureImages['principal'])) }}" class="cover-sig-img" alt="Principal Signature" />
                                <div class="cover-sig-line has-img">{{ $principalName }}</div>
                            @else
                                <div class="cover-sig-spacer"></div>
                                <div class="cover-sig-line">{{ $principalName }}</div>
                            @endif
                            <div class="cover-sig-sub">{{ $principalTitle }} / Head of School</div>
                            <div class="cover-seal-stamp">OFFICIAL SEAL & SIGNATURE</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
