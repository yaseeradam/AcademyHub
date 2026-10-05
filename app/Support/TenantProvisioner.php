<?php

namespace App\Support;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Tenant;
use App\Support\TenantSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TenantProvisioner
{
    public function provision(Tenant $tenant): void
    {
        $this->ensureSettingsFile($tenant);
        // Academic sessions & terms, classes, sections, subjects and fee structures
        // are NOT auto-created. Each school must explicitly configure their current
        // academic calendar upon first login.
    }

    private function ensureSettingsFile(Tenant $tenant): void
    {
        $path = storage_path('app/academyhub/tenants/'.$tenant->id.'/settings.json');

        File::ensureDirectoryExists(dirname($path));

        $settings = array_filter([
            'school_name'  => $tenant->name,
            'school_email' => $tenant->contact_email ?: null,
            'school_phone' => $tenant->contact_phone ?: null,
        ], static fn ($v) => $v !== null && $v !== '');

        $settings['subscription_fee_per_student'] = $tenant->subscription_fee_per_student;
        $settings['subscription_due_date'] = $tenant->expires_at
            ? $tenant->expires_at->toDateString()
            : null;

        // Always write (overwrite) so the name is always correct
        File::put($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Bust the settings cache so the new name loads immediately
        Cache::forget(TenantSettings::settingsCacheKey($tenant));
    }

    // ensureAcademicCalendar(), ensureDefaultClassesAndSections(), ensureDefaultSubjects()
    // and ensureDefaultFeeStructures() have been intentionally removed.
    // Schools must set their own academic calendar, classes, sections, subjects
    // and fee structures through the admin interface after onboarding.
}
