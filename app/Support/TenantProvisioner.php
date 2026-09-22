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
        $this->ensureAcademicCalendar($tenant);
        // Classes, sections, subjects and fee structures are NOT auto-created.
        // Each school adds their own after onboarding.
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

    private function ensureAcademicCalendar(Tenant $tenant): void
    {
        $session = AcademicSession::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();

        if (! $session) {
            $year = (int) now()->format('Y');
            $next = $year + 1;
            $defaultSession = "{$year}/{$next}";

            $session = AcademicSession::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $defaultSession],
                ['is_active' => true]
            );
        }

        $hasActiveTerm = AcademicTerm::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->exists();

        $termDefaults = [
            1 => 'First Term',
            2 => 'Second Term',
            3 => 'Third Term',
        ];

        foreach ($termDefaults as $termNumber => $name) {
            AcademicTerm::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'academic_session_id' => $session->id,
                    'term_number' => $termNumber,
                ],
                [
                    'name' => $name,
                    'is_active' => false,
                ]
            );
        }

        if (! $hasActiveTerm) {
            AcademicTerm::query()
                ->where('tenant_id', $tenant->id)
                ->update(['is_active' => false]);

            AcademicTerm::query()
                ->where('tenant_id', $tenant->id)
                ->where('academic_session_id', $session->id)
                ->where('term_number', 1)
                ->update(['is_active' => true]);
        }
    }

    // ensureDefaultClassesAndSections(), ensureDefaultSubjects() and
    // ensureDefaultFeeStructures() have been intentionally removed.
    // Schools must create their own classes, sections, subjects and fee
    // structures through the admin interface after onboarding.
}
