<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Report Card - <?php echo e($student->admission_number); ?></title>
    <style>
        @page {
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #fff;
        }

        .page-border {
            border: 4px solid #d97706;
            padding: 12px;
            background: linear-gradient(to bottom, #fffbeb 0%, #ffffff 100%);
            position: relative;
            box-shadow: inset 0 0 0 2px #fbbf24;
        }

        /* ─── Header ─── */
        .header {
            text-align: center;
            border-bottom: 4px double #d97706;
            padding-bottom: 16px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1);
        }

        .header-flex {
            display: table;
            width: 100%;
        }

        .header-logo {
            display: table-cell;
            width: 90px;
            vertical-align: middle;
            text-align: center;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border: 2px solid #d97706;
            border-radius: 50%;
            padding: 4px;
            background: white;
        }

        .school-name {
            font-size: 24px;
            font-weight: 900;
            background: linear-gradient(135deg, #92400e, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 4px;
        }

        .school-motto {
            font-size: 10px;
            color: #b45309;
            font-style: italic;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .school-info {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .report-title {
            display: inline-block;
            font-size: 14px;
            font-weight: 900;
            color: white;
            background: linear-gradient(135deg, #d97706, #b45309, #92400e);
            padding: 9px 32px;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4);
        }

        /* ─── Session Bar ─── */
        .session-bar {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid #fbbf24;
            padding: 10px 14px;
            margin-bottom: 14px;
            border-radius: 8px;
            display: table;
            width: 100%;
            box-shadow: 0 2px 6px rgba(251, 191, 36, 0.2);
        }

        .session-item {
            display: table-cell;
            width: 33.33%;
            font-size: 9px;
            padding: 0 8px;
        }

        .session-label {
            color: #92400e;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            display: block;
            margin-bottom: 2px;
        }

        .session-value {
            color: #78350f;
            font-weight: 800;
            font-size: 11px;
        }

        /* ─── Student Info (compact inline) ─── */
        .student-section {
            border: 3px solid #d97706;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            box-shadow: 0 3px 10px rgba(217, 119, 6, 0.15);
        }

        .student-row-inline {
            display: table;
            width: 100%;
            border-bottom: 1px dotted #fbbf24;
        }

        .student-row-inline:last-child {
            border-bottom: none;
        }

        .student-cell {
            display: table-cell;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .student-label {
            color: #92400e;
            font-weight: 700;
            font-size: 7px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .student-value {
            font-weight: 700;
            color: #1f2937;
            font-size: 9px;
        }

        .passport-cell {
            display: table-cell;
            width: 70px;
            text-align: center;
            vertical-align: middle;
            padding-left: 8px;
        }

        .passport {
            width: 60px;
            height: 72px;
            border: 2px solid #d97706;
            border-radius: 6px;
            object-fit: cover;
        }

        /* ─── Summary Cards ─── */
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .summary-card {
            display: table-cell;
            padding: 4px;
        }

        .summary-inner {
            background: linear-gradient(135deg, #d97706, #b45309);
            border-radius: 8px;
            padding: 8px 4px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .summary-inner.green {
            background: linear-gradient(135deg, #059669, #047857);
        }

        .summary-inner.blue {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .summary-inner.purple {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
        }

        .summary-inner.teal {
            background: linear-gradient(135deg, #0d9488, #0f766e);
        }

        .summary-inner.rose {
            background: linear-gradient(135deg, #e11d48, #be123c);
        }

        .summary-label {
            font-size: 8px;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: 900;
            color: white;
        }

        /* ─── Scores Table ─── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border: 3px solid #d97706;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(217, 119, 6, 0.15);
        }

        th {
            background: linear-gradient(135deg, #d97706, #b45309, #92400e);
            color: white;
            padding: 7px 4px;
            text-align: center;
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #b45309;
        }

        td {
            padding: 4px 4px;
            border: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
        }

        tr:nth-child(even) {
            background: #fffbeb;
        }

        .subject-name {
            text-align: left;
            font-weight: 700;
            color: #78350f;
        }

        .grade-a {
            color: #059669;
            font-weight: 800;
        }

        .grade-b {
            color: #2563eb;
            font-weight: 800;
        }

        .grade-c {
            color: #d97706;
            font-weight: 800;
        }

        .grade-d {
            color: #ea580c;
            font-weight: 800;
        }

        .grade-f {
            color: #dc2626;
            font-weight: 800;
        }

        /* ─── Grading Key ─── */
        .grading-key {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid #fbbf24;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 14px;
            box-shadow: 0 2px 6px rgba(251, 191, 36, 0.2);
        }

        .grading-title {
            font-size: 9px;
            font-weight: 800;
            color: #92400e;
            margin-bottom: 6px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .grading-grid {
            display: table;
            width: 100%;
        }

        .grading-item {
            display: table-cell;
            text-align: center;
            font-size: 8px;
            padding: 4px;
            font-weight: 600;
            color: #78350f;
        }

        .grade-letter {
            font-weight: 800;
            font-size: 10px;
        }

        /* ─── Attendance ─── */
        .attendance-box {
            border: 2px solid #fbbf24;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            box-shadow: 0 2px 6px rgba(251, 191, 36, 0.2);
        }

        .attendance-title {
            font-size: 9px;
            font-weight: 800;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            text-align: center;
        }

        .attendance-grid {
            display: table;
            width: 100%;
        }

        .attendance-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 6px 4px;
        }

        .attendance-inner {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 4px;
        }

        .attendance-label {
            font-size: 7px;
            color: #92400e;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .attendance-value {
            font-size: 16px;
            font-weight: 800;
            color: #1f2937;
        }

        /* ─── Psychomotor Domain ─── */
        .psychomotor-box {
            border: 2px solid #fbbf24;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            box-shadow: 0 2px 6px rgba(251, 191, 36, 0.2);
        }

        .psychomotor-title {
            font-size: 9px;
            font-weight: 800;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-align: center;
        }

        .psychomotor-grid {
            display: table;
            width: 100%;
        }

        .psychomotor-row {
            display: table-row;
        }

        .psychomotor-cell {
            display: table-cell;
            width: 50%;
            padding: 3px 6px;
        }

        .psychomotor-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 4px;
        }

        .psychomotor-label {
            font-size: 8px;
            color: #78350f;
            font-weight: 700;
            display: inline-block;
            width: 60%;
        }

        .psychomotor-rating {
            font-size: 8px;
            font-weight: 600;
            color: #6b7280;
            display: inline-block;
            width: 35%;
            text-align: right;
        }

        .rating-bar {
            display: inline-block;
            width: 40px;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            vertical-align: middle;
            margin-left: 4px;
        }

        .rating-fill {
            height: 100%;
            border-radius: 3px;
        }

        .rating-fill.excellent { background: #059669; width: 100%; }
        .rating-fill.good { background: #2563eb; width: 75%; }
        .rating-fill.average { background: #d97706; width: 50%; }
        .rating-fill.fair { background: #ea580c; width: 25%; }

        /* ─── School Fees ─── */
        .school-fees-box {
            border: 3px solid #d97706;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            box-shadow: 0 3px 10px rgba(217, 119, 6, 0.15);
        }

        .school-fees-title {
            font-size: 10px;
            font-weight: 900;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
            text-align: center;
        }

        .school-fees-grid {
            display: table;
            width: 100%;
        }

        .school-fees-item {
            display: table-cell;
            padding: 4px 8px;
            vertical-align: top;
        }

        .school-fees-label {
            font-size: 7px;
            color: #92400e;
            font-weight: 700;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .school-fees-value {
            font-size: 10px;
            font-weight: 800;
            color: #78350f;
        }

        .school-fees-amount {
            font-size: 16px;
            font-weight: 900;
            color: #92400e;
            text-align: center;
            padding: 8px;
            background: white;
            border: 2px solid #d97706;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        /* ─── Remarks ─── */
        .remarks-section {
            border: 2px solid #fbbf24;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            box-shadow: 0 2px 6px rgba(251, 191, 36, 0.15);
        }

        .remarks-title {
            font-size: 9px;
            font-weight: 800;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .remarks-content {
            min-height: 30px;
            border-bottom: 1px solid #d97706;
            padding: 4px 0;
            font-size: 9px;
            color: #374151;
        }

        /* ─── Next Term ─── */
        .next-term {
            background: linear-gradient(135deg, #d97706, #b45309, #92400e);
            padding: 10px;
            text-align: center;
            font-size: 11px;
            font-weight: 900;
            color: white;
            margin-bottom: 12px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        /* ─── Signatures ─── */
        .signature-section {
            margin-top: 20px;
            display: table;
            width: 100%;
        }

        .signature {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px;
        }

        .signature-image {
            max-height: 40px;
            max-width: 100px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .signature-line {
            border-top: 3px double #78350f;
            margin-top: 40px;
            padding-top: 6px;
            font-size: 10px;
            font-weight: 900;
            color: #78350f;
        }

        .signature-line.has-image {
            margin-top: 6px;
        }

        .signature-label {
            font-size: 7px;
            color: #9ca3af;
            margin-top: 2px;
            font-style: italic;
        }

        /* ─── Footer ─── */
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 3px double #d97706;
            text-align: center;
            font-size: 8px;
            color: #78350f;
            font-weight: 600;
        }

        /* ─── Watermark ─── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0.03;
            width: 400px;
            height: 400px;
        }
    </style>
</head>

<body>
    <?php
        $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
        $logo = config('myacademy.school_logo');
        $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;
        $logoExists = $logoPath && file_exists($logoPath);

        // Report card options (with safe defaults)
        $opts = $rcOptions ?? [];
        $showPosition = $opts['show_position'] ?? true;
        $showAttendance = $opts['show_attendance'] ?? true;
        $showGradingKey = $opts['show_grading_key'] ?? true;
        $showClassAverage = $opts['show_class_average'] ?? true;
        $showWatermark = $opts['show_watermark'] ?? true;
        $showNextTermDate = $opts['show_next_term_date'] ?? true;
        $showTeacherRemarks = $opts['show_teacher_remarks'] ?? true;
        $showPrincipalRemarks = $opts['show_principal_remarks'] ?? true;
        $showPsychomotor = $opts['show_psychomotor'] ?? false;
        $showSchoolFees = $opts['show_school_fees'] ?? false;
        $showSignatures = $opts['show_signatures'] ?? false;
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists && $showWatermark): ?>
        <div class="watermark">
            <img src="<?php echo e($logoPath); ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;" />
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="page-border">
        <div class="header">
            <div class="header-flex">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists): ?>
                    <div class="header-logo">
                        <img src="<?php echo e($logoPath); ?>" alt="Logo" class="logo" />
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="header-center">
                    <div class="school-name"><?php echo e($schoolName); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_motto')): ?>
                        <div class="school-motto">"<?php echo e(config('myacademy.school_motto')); ?>"</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_address')): ?>
                        <div class="school-info"><?php echo e(config('myacademy.school_address')); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_phone') || config('myacademy.school_email')): ?>
                        <div class="school-info">
                            <?php echo e(config('myacademy.school_phone')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_phone') && config('myacademy.school_email')): ?> • <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e(config('myacademy.school_email')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="report-title">Student Report Card</div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists): ?>
                    <div class="header-logo">
                        <img src="<?php echo e($logoPath); ?>" alt="Logo" class="logo" />
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="session-bar">
            <div class="session-item">
                <span class="session-label">Academic Session</span>
                <span class="session-value"><?php echo e($session); ?></span>
            </div>
            <div class="session-item">
                <span class="session-label">Term</span>
                <span class="session-value">Term <?php echo e($term); ?></span>
            </div>
            <div class="session-item">
                <span class="session-label">Report Date</span>
                <span class="session-value"><?php echo e(now()->format('d M, Y')); ?></span>
            </div>
        </div>

        <div class="student-section">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; vertical-align: middle;">
                    <div class="student-row-inline">
                        <div class="student-cell">
                            <span class="student-label">Student Name:</span>
                            <span class="student-value"> <?php echo e($student->full_name); ?></span>
                        </div>
                        <div class="student-cell">
                            <span class="student-label">Admission No:</span>
                            <span class="student-value"> <?php echo e($student->admission_number); ?></span>
                        </div>
                        <div class="student-cell">
                            <span class="student-label">Class / Section:</span>
                            <span class="student-value"> <?php echo e($student->schoolClass?->name); ?> <?php echo e($student->section?->name ? '— ' . $student->section->name : ''); ?></span>
                        </div>
                    </div>
                    <div class="student-row-inline">
                        <div class="student-cell">
                            <span class="student-label">Gender:</span>
                            <span class="student-value"> <?php echo e($student->gender ?? 'N/A'); ?></span>
                        </div>
                        <div class="student-cell">
                            <span class="student-label">Date of Birth:</span>
                            <span class="student-value"> <?php echo e($student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M, Y') : 'N/A'); ?></span>
                        </div>
                        <div class="student-cell">
                            <span class="student-label">No. in Class:</span>
                            <span class="student-value"> <?php echo e($totalStudents ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo): ?>
                    <div class="passport-cell">
                        <img src="<?php echo e(public_path('uploads/' . str_replace('\\', '/', $student->passport_photo))); ?>"
                            alt="Photo" class="passport" />
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-inner">
                    <div class="summary-label">Total Score</div>
                    <div class="summary-value"><?php echo e($grandTotal); ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-inner green">
                    <div class="summary-label">Average</div>
                    <div class="summary-value"><?php echo e(number_format($average, 1)); ?>%</div>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPosition): ?>
            <div class="summary-card">
                <div class="summary-inner blue">
                    <div class="summary-label">Position</div>
                    <div class="summary-value"><?php echo e($position); ?></div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassAverage): ?>
            <div class="summary-card">
                <div class="summary-inner purple">
                    <div class="summary-label">Class Avg</div>
                    <div class="summary-value"><?php echo e(number_format($classAverage, 1)); ?>%</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-inner teal">
                    <div class="summary-label">Highest</div>
                    <div class="summary-value"><?php echo e(number_format($highestAverage ?? 0, 1)); ?>%</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-inner rose">
                    <div class="summary-label">Lowest</div>
                    <div class="summary-value"><?php echo e(number_format($lowestAverage ?? 0, 1)); ?>%</div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 35%; text-align: left; padding-left: 8px;">Subject</th>
                    <th style="width: 11%;">CA1<br />(<?php echo e(config('myacademy.results_ca1_max', 20)); ?>)</th>
                    <th style="width: 11%;">CA2<br />(<?php echo e(config('myacademy.results_ca2_max', 20)); ?>)</th>
                    <th style="width: 11%;">Exam<br />(<?php echo e(config('myacademy.results_exam_max', 60)); ?>)</th>
                    <th style="width: 11%;">Total<br />(100)</th>
                    <th style="width: 10%;">Grade</th>
                    <th style="width: 11%;">Class<br />Avg</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $grade = $row['grade'] ?? '';
                        $gradeClass = match (strtoupper($grade)) {
                            'A' => 'grade-a',
                            'B' => 'grade-b',
                            'C' => 'grade-c',
                            'D' => 'grade-d',
                            'F' => 'grade-f',
                            default => '',
                        };
                    ?>
                    <tr>
                        <td class="subject-name"><?php echo e($row['subject']->name); ?></td>
                        <td><?php echo e($row['ca1'] ?? '—'); ?></td>
                        <td><?php echo e($row['ca2'] ?? '—'); ?></td>
                        <td><?php echo e($row['exam'] ?? '—'); ?></td>
                        <td><strong><?php echo e($row['total'] ?? '—'); ?></strong></td>
                        <td class="<?php echo e($gradeClass); ?>"><?php echo e($grade ?: '—'); ?></td>
                        <td><?php echo e($row['class_avg'] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGradingKey): ?>
        <div class="grading-key">
            <div class="grading-title">Grading System</div>
            <div class="grading-grid">
                <div class="grading-item"><span class="grade-letter grade-a">A:</span> 70-100 (Excellent)</div>
                <div class="grading-item"><span class="grade-letter grade-b">B:</span> 60-69 (Very Good)</div>
                <div class="grading-item"><span class="grade-letter grade-c">C:</span> 50-59 (Good)</div>
                <div class="grading-item"><span class="grade-letter grade-d">D:</span> 40-49 (Pass)</div>
                <div class="grading-item"><span class="grade-letter grade-f">F:</span> 0-39 (Fail)</div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAttendance): ?>
        <div class="attendance-box">
            <div class="attendance-title">Attendance Record</div>
            <div class="attendance-grid">
                <div class="attendance-item">
                    <div class="attendance-inner">
                        <div class="attendance-label">Times Opened</div>
                        <div class="attendance-value"><?php echo e($timesOpened ?? '—'); ?></div>
                    </div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-inner">
                        <div class="attendance-label">Times Present</div>
                        <div class="attendance-value"><?php echo e($timesPresent ?? '—'); ?></div>
                    </div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-inner">
                        <div class="attendance-label">Times Absent</div>
                        <div class="attendance-value"><?php echo e($timesAbsent ?? '—'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPsychomotor): ?>
        <div class="psychomotor-box">
            <div class="psychomotor-title">Psychomotor Domain / Affective Skills</div>
            <div class="psychomotor-grid">
                <?php
                    $psychomotorTraits = [
                        ['name' => 'Handwriting', 'rating' => 'Good'],
                        ['name' => 'Verbal Fluency', 'rating' => 'Good'],
                        ['name' => 'Games / Sports', 'rating' => 'Average'],
                        ['name' => 'Craft / Drawing', 'rating' => 'Good'],
                        ['name' => 'Musical Skills', 'rating' => 'Average'],
                        ['name' => 'Punctuality', 'rating' => 'Excellent'],
                        ['name' => 'Neatness', 'rating' => 'Good'],
                        ['name' => 'Politeness / Courtesy', 'rating' => 'Excellent'],
                        ['name' => 'Honesty', 'rating' => 'Excellent'],
                        ['name' => 'Self-Control', 'rating' => 'Good'],
                        ['name' => 'Attentiveness', 'rating' => 'Good'],
                        ['name' => 'Relationship with Others', 'rating' => 'Excellent'],
                    ];
                    $chunked = array_chunk($psychomotorTraits, 2);
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $chunked; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="psychomotor-row">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pair; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trait): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="psychomotor-cell">
                        <div class="psychomotor-item">
                            <span class="psychomotor-label"><?php echo e($trait['name']); ?></span>
                            <span class="psychomotor-rating">
                                <?php echo e($trait['rating']); ?>

                                <span class="rating-bar">
                                    <span class="rating-fill <?php echo e(strtolower($trait['rating'])); ?>"></span>
                                </span>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTeacherRemarks): ?>
        <div class="remarks-section">
            <div class="remarks-title">Class Teacher's Remarks</div>
            <div class="remarks-content"><?php echo e($teacherRemarks ?? 'No remarks provided.'); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPrincipalRemarks): ?>
        <div class="remarks-section">
            <div class="remarks-title">Principal's Remarks</div>
            <div class="remarks-content"><?php echo e($principalRemarks ?? 'No remarks provided.'); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSchoolFees && !empty($schoolFees)): ?>
        <div class="school-fees-box">
            <div class="school-fees-title">School Fees for Next Term</div>
            <div class="school-fees-amount">
                <?php echo e($schoolFees['currency'] ?? '₦'); ?><?php echo e(number_format($schoolFees['amount'], 2)); ?>

            </div>
            <div class="school-fees-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolFees['bank_name'] ?? null): ?>
                <div class="school-fees-item">
                    <span class="school-fees-label">Bank Name</span>
                    <span class="school-fees-value"><?php echo e($schoolFees['bank_name']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolFees['account_number'] ?? null): ?>
                <div class="school-fees-item">
                    <span class="school-fees-label">Account Number</span>
                    <span class="school-fees-value"><?php echo e($schoolFees['account_number']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolFees['account_name'] ?? null): ?>
                <div class="school-fees-item">
                    <span class="school-fees-label">Account Name</span>
                    <span class="school-fees-value"><?php echo e($schoolFees['account_name']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNextTermDate): ?>
        <div class="next-term">
            Next Term Begins: <?php echo e($nextTermDate ?? 'To be announced'); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="signature-section">
            <div class="signature">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSignatures && ($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher'])): ?>
                    <img src="<?php echo e($signatureImages['teacher']); ?>" alt="Teacher Signature" class="signature-image" />
                    <div class="signature-line has-image">Class Teacher</div>
                <?php else: ?>
                    <div class="signature-line">Class Teacher</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="signature-label">Signature & Date</div>
            </div>
            <div class="signature">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSignatures && ($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal'])): ?>
                    <img src="<?php echo e($signatureImages['principal']); ?>" alt="Principal Signature" class="signature-image" />
                    <div class="signature-line has-image">Principal</div>
                <?php else: ?>
                    <div class="signature-line">Principal</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="signature-label">Signature & Stamp</div>
            </div>
            <div class="signature">
                <div class="signature-line">Parent/Guardian</div>
                <div class="signature-label">Signature & Date</div>
            </div>
        </div>

        <div class="footer">
            Generated on <?php echo e(now()->format('l, F j, Y \a\t g:i A')); ?> • <?php echo e($schoolName); ?> • Powered by MyAcademy SMS
        </div>
    </div>
</body>

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/report-card.blade.php ENDPATH**/ ?>