<?php

return [
    'mode' => env('ACADEMYHUB_MODE', 'full'), // full|cbt
    'premium_enforce' => false, // Disabled - all features are free

    // Used to bind a license to a specific school installation/customer.
    // Recommended: set a unique value (UUID) per client deployment and issue licenses for that school_id.
    'school_id' => env('ACADEMYHUB_SCHOOL_ID', ''),

    'school_name' => env('ACADEMYHUB_SCHOOL_NAME', env('APP_NAME', 'AcademyHub')),
    'tagline' => env('ACADEMYHUB_SCHOOL_TAGLINE', "Here's what's happening in your school today."),
    'current_week' => env('ACADEMYHUB_CURRENT_WEEK', 'Week 1'),
    'currency_symbol' => env('ACADEMYHUB_CURRENCY_SYMBOL', '₦'),

    'school_address' => env('ACADEMYHUB_SCHOOL_ADDRESS'),
    'school_phone' => env('ACADEMYHUB_SCHOOL_PHONE'),
    'school_email' => env('ACADEMYHUB_SCHOOL_EMAIL'),
    'school_logo' => env('ACADEMYHUB_SCHOOL_LOGO'),

    'results_ca1_max' => (int) env('ACADEMYHUB_RESULTS_CA1_MAX', 20),
    'results_ca2_max' => (int) env('ACADEMYHUB_RESULTS_CA2_MAX', 20),
    'results_exam_max' => (int) env('ACADEMYHUB_RESULTS_EXAM_MAX', 60),

    'certificate_orientation' => env('ACADEMYHUB_CERTIFICATE_ORIENTATION', 'landscape'),
    'certificate_border_color' => env('ACADEMYHUB_CERTIFICATE_BORDER_COLOR', '#0ea5e9'),
    'certificate_accent_color' => env('ACADEMYHUB_CERTIFICATE_ACCENT_COLOR', '#0ea5e9'),
    'certificate_show_logo' => (bool) env('ACADEMYHUB_CERTIFICATE_SHOW_LOGO', true),
    'certificate_show_watermark' => (bool) env('ACADEMYHUB_CERTIFICATE_SHOW_WATERMARK', false),
    'certificate_watermark_image' => env('ACADEMYHUB_CERTIFICATE_WATERMARK_IMAGE'),

    'certificate_signature_label' => env('ACADEMYHUB_CERTIFICATE_SIGNATURE_LABEL', 'Authorized Signature'),
    'certificate_signature_name' => env('ACADEMYHUB_CERTIFICATE_SIGNATURE_NAME'),
    'certificate_signature_image' => env('ACADEMYHUB_CERTIFICATE_SIGNATURE_IMAGE'),

    'certificate_signature2_label' => env('ACADEMYHUB_CERTIFICATE_SIGNATURE2_LABEL'),
    'certificate_signature2_name' => env('ACADEMYHUB_CERTIFICATE_SIGNATURE2_NAME'),
    'certificate_signature2_image' => env('ACADEMYHUB_CERTIFICATE_SIGNATURE2_IMAGE'),

    'certificate_default_type' => env('ACADEMYHUB_CERTIFICATE_DEFAULT_TYPE', 'General'),
    'certificate_default_title' => env('ACADEMYHUB_CERTIFICATE_DEFAULT_TITLE', 'Certificate'),
    'certificate_default_body' => env('ACADEMYHUB_CERTIFICATE_DEFAULT_BODY'),
    'certificate_template' => env('ACADEMYHUB_CERTIFICATE_TEMPLATE', 'modern'),

    'report_card_template' => env('ACADEMYHUB_REPORT_CARD_TEMPLATE', 'compact'),

    // Report Card display options
    'rc_show_position' => true,
    'rc_show_attendance' => true,
    'rc_show_grading_key' => true,
    'rc_show_class_average' => true,
    'rc_show_watermark' => true,
    'rc_show_next_term_date' => true,
    'rc_show_teacher_remarks' => true,
    'rc_show_principal_remarks' => true,
    'rc_show_psychomotor' => false,
    'rc_psychomotor_style' => 'progress',
    'rc_show_school_fees' => false,
    'rc_school_fees_account_number' => null,
    'rc_school_fees_bank_name' => null,
    'rc_school_fees_account_name' => null,
    'rc_school_fees_by_class' => null, // JSON: {"class_id": amount}
    'rc_show_signatures' => false,
    'rc_principal_signature_image' => null,
    'rc_teacher_signature_image' => null,

    'premium_device_removal_limit' => (int) env('ACADEMYHUB_PREMIUM_DEVICE_REMOVAL_LIMIT', 2),
    'premium_device_removal_window_days' => (int) env('ACADEMYHUB_PREMIUM_DEVICE_REMOVAL_WINDOW_DAYS', 30),

    // Premium licensing (Ed25519)
    // Set this to the base64 encoded Ed25519 public key used to verify licenses.
    'license_public_key' => env('ACADEMYHUB_LICENSE_PUBLIC_KEY', ''),
];
