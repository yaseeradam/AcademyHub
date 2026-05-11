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
        $this->ensureDefaultClassesAndSections($tenant);
        $this->ensureDefaultSubjects($tenant);
        $this->ensureDefaultFeeStructures($tenant);
    }

    private function ensureSettingsFile(Tenant $tenant): void
    {
        $path = storage_path('app/myacademy/tenants/'.$tenant->id.'/settings.json');

        File::ensureDirectoryExists(dirname($path));

        $settings = array_filter([
            'school_name'  => $tenant->name,
            'school_email' => $tenant->contact_email ?: null,
            'school_phone' => $tenant->contact_phone ?: null,
        ], static fn ($v) => $v !== null && $v !== '');

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

    private function ensureDefaultClassesAndSections(Tenant $tenant): void
    {
        $classRows = [
            ['name' => 'JSS 1', 'level' => 1],
            ['name' => 'JSS 2', 'level' => 2],
            ['name' => 'JSS 3', 'level' => 3],
            ['name' => 'SSS 1', 'level' => 4],
            ['name' => 'SSS 2', 'level' => 5],
            ['name' => 'SSS 3', 'level' => 6],
        ];

        foreach ($classRows as $row) {
            $class = SchoolClass::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $row['name']],
                ['level' => $row['level']]
            );

            foreach (['A', 'B', 'C'] as $sectionName) {
                Section::query()->firstOrCreate([
                    'tenant_id' => $tenant->id,
                    'class_id' => $class->id,
                    'name' => $sectionName,
                ]);
            }
        }
    }

    private function ensureDefaultSubjects(Tenant $tenant): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MTH'],
            ['name' => 'English Language', 'code' => 'ENG'],
            ['name' => 'Basic Science', 'code' => 'BSC'],
            ['name' => 'Social Studies', 'code' => 'SOS'],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $subject['code']],
                ['name' => $subject['name']]
            );
        }
    }

    private function ensureDefaultFeeStructures(Tenant $tenant): void
    {
        $defaultTuitionByClassLevel = [
            1 => 40000,
            2 => 45000,
            3 => 45000,
            4 => 55000,
            5 => 55000,
            6 => 60000,
        ];

        $classes = SchoolClass::query()->where('tenant_id', $tenant->id)->get();
        foreach ($classes as $class) {
            $amount = $defaultTuitionByClassLevel[(int) ($class->level ?? 0)] ?? 50000;

            FeeStructure::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'class_id' => $class->id,
                    'category' => 'Tuition',
                    'term' => null,
                    'session' => null,
                ],
                [
                    'amount_due' => $amount,
                ]
            );
        }
    }
}
