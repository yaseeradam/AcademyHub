<?php

namespace App\Providers;

use App\Models\Student;
use App\Observers\StudentObserver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Student::observe(StudentObserver::class);



        $settings = \Illuminate\Support\Facades\Cache::rememberForever('myacademy_settings_cache', function () {
            $path = storage_path('app/myacademy/settings.json');

            if (!File::exists($path)) {
                return [];
            }

            $data = json_decode(File::get($path), true);
            if (!is_array($data)) {
                return [];
            }

            $allowed = [
                'school_name',
                'school_address',
                'school_phone',
                'school_email',
                'school_logo',
                'currency_symbol',
                'current_week',
                'tagline',
                'results_ca1_max',
                'results_ca2_max',
                'results_exam_max',
                'certificate_orientation',
                'certificate_border_color',
                'certificate_accent_color',
                'certificate_show_logo',
                'certificate_show_watermark',
                'certificate_watermark_image',
                'certificate_signature_label',
                'certificate_signature_name',
                'certificate_signature_image',
                'certificate_signature2_label',
                'certificate_signature2_name',
                'certificate_signature2_image',
                'certificate_default_type',
                'certificate_default_title',
                'certificate_default_body',
                'certificate_template',
                'report_card_template',
                'rc_show_position',
                'rc_show_attendance',
                'rc_show_grading_key',
                'rc_show_class_average',
                'rc_show_watermark',
                'rc_show_next_term_date',
                'rc_show_teacher_remarks',
                'rc_show_principal_remarks',
                'rc_show_psychomotor',
                'rc_show_school_fees',
                'rc_school_fees_account_number',
                'rc_school_fees_bank_name',
                'rc_school_fees_account_name',
                'rc_school_fees_by_class',
                'rc_show_signatures',
                'rc_principal_signature_image',
                'rc_teacher_signature_image',
            ];

            return Arr::only($data, $allowed);
        });

        foreach ($settings as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // Handle boolean values explicitly
            if (in_array($key, [
                'certificate_show_logo', 'certificate_show_watermark',
                'rc_show_position', 'rc_show_attendance', 'rc_show_grading_key',
                'rc_show_class_average', 'rc_show_watermark', 'rc_show_next_term_date',
                'rc_show_teacher_remarks', 'rc_show_principal_remarks',
                'rc_show_psychomotor', 'rc_show_school_fees', 'rc_show_signatures'
            ])) {
                $value = (bool) $value;
            }

            config(["myacademy.{$key}" => $value]);
        }
    }
}
