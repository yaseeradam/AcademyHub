{{-- Shared Report Card Options Initialization --}}
@php
    $opts = $rcOptions ?? [];
    $showPosition                  = $opts['show_position'] ?? true;
    $showAttendance                = $opts['show_attendance'] ?? true;
    $showGradingKey                = $opts['show_grading_key'] ?? true;
    $showClassAverage              = $opts['show_class_average'] ?? true;
    $showWatermark                 = $opts['show_watermark'] ?? true;
    $showNextTermDate              = $opts['show_next_term_date'] ?? true;
    $showTeacherRemarks            = $opts['show_teacher_remarks'] ?? true;
    $showPrincipalRemarks          = $opts['show_principal_remarks'] ?? true;
    $showPsychomotor               = $opts['show_psychomotor'] ?? false;
    $showSchoolFees                = $opts['show_school_fees'] ?? false;
    $showSignatures                = $opts['show_signatures'] ?? false;
    $showClassHighestLowest        = $opts['show_class_highest_lowest'] ?? false;
    $showSubjectTeacherRemarks     = $opts['show_subject_teacher_remarks'] ?? false;
    $showQrCode                    = $opts['show_qr_code'] ?? true;
    $showCumulativeSummary         = $opts['show_cumulative_summary'] ?? false;
    $showColorBadges               = $opts['show_color_badges'] ?? true;
@endphp
