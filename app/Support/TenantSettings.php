<?php

namespace App\Support;

class TenantSettings
{
    public static function tenantId(): ?int
    {
        if (! app()->bound('currentTenant')) {
            return null;
        }

        $tenant = app('currentTenant');
        if (! $tenant || ! isset($tenant->id)) {
            return null;
        }

        $id = (int) $tenant->id;

        return $id > 0 ? $id : null;
    }

    public static function settingsPath(): string
    {
        $tenantId = self::tenantId();

        return $tenantId
            ? storage_path('app/academyhub/tenants/'.$tenantId.'/settings.json')
            : storage_path('app/academyhub/settings.json');
    }

    public static function settingsCacheKey(?\App\Models\Tenant $tenant = null): string
    {
        $tenantId = $tenant?->id ?? self::tenantId();

        return $tenantId
            ? 'academyhub_settings_cache_tenant_'.$tenantId
            : 'academyhub_settings_cache_global';
    }

    public static function uploadsSubdir(string $baseDir): string
    {
        $tenantId = self::tenantId();
        if (! $tenantId) {
            return $baseDir;
        }

        return rtrim($baseDir, '/').'/tenant_'.$tenantId;
    }

    public static function loadToConfig(): void
    {
        $settings = \Illuminate\Support\Facades\Cache::rememberForever(self::settingsCacheKey(), function () {
            $path = self::settingsPath();

            if (! \Illuminate\Support\Facades\File::exists($path)) {
                return [];
            }

            $data = json_decode(\Illuminate\Support\Facades\File::get($path), true);
            if (! is_array($data)) {
                return [];
            }

            $allowed = [
                'school_name',
                'school_address',
                'school_phone',
                'school_email',
                'school_logo',
                'school_motto',
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
                'rc_psychomotor_style',
                'rc_show_school_fees',
                'rc_school_fees_account_number',
                'rc_school_fees_bank_name',
                'rc_school_fees_account_name',
                'rc_school_fees_by_class',
                'rc_show_signatures',
                'rc_principal_signature_image',
                'rc_teacher_signature_image',
                'rc_show_class_highest_lowest',
                'rc_show_subject_teacher_remarks',
                'rc_show_qr_code',
                'rc_show_cumulative_summary',
                'rc_show_color_badges',
                'subscription_due_date',
            ];

            return \Illuminate\Support\Arr::only($data, $allowed);
        });

        foreach ($settings as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($key, [
                'certificate_show_logo', 'certificate_show_watermark',
                'rc_show_position', 'rc_show_attendance', 'rc_show_grading_key',
                'rc_show_class_average', 'rc_show_watermark', 'rc_show_next_term_date',
                'rc_show_teacher_remarks', 'rc_show_principal_remarks',
                'rc_show_psychomotor', 'rc_show_school_fees', 'rc_show_signatures',
                'rc_show_class_highest_lowest', 'rc_show_subject_teacher_remarks',
                'rc_show_qr_code', 'rc_show_cumulative_summary', 'rc_show_color_badges',
            ], true)) {
                $value = (bool) $value;
            }

            config(["myacademy.{$key}" => $value]);
            config(["academyhub.{$key}" => $value]);
        }
    }
}

