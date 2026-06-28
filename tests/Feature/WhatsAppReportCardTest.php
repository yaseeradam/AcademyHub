<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WhatsAppReportCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_report_card_compact_template_contains_subject_names(): void
    {
        config(['services.whatsapp.api_key' => 'test-api-key']);

        $tenant = $this->createTenantWithTemplate('compact');

        $class = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'JSS 1A',
            'level' => 1,
        ]);
        $section = Section::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'A',
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);

        $maths = Subject::query()->create(['tenant_id' => $tenant->id, 'name' => 'Mathematics', 'code' => 'MTH']);
        $english = Subject::query()->create(['tenant_id' => $tenant->id, 'name' => 'English', 'code' => 'ENG']);

        SubjectAllocation::query()->create([
            'tenant_id' => $tenant->id,
            'teacher_id' => User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'teacher'])->id,
            'subject_id' => $maths->id,
            'class_id' => $class->id,
        ]);
        SubjectAllocation::query()->create([
            'tenant_id' => $tenant->id,
            'teacher_id' => User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'teacher'])->id,
            'subject_id' => $english->id,
            'class_id' => $class->id,
        ]);

        Score::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'subject_id' => $maths->id,
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'ca1' => 18,
            'ca2' => 17,
            'exam' => 56,
        ]);
        Score::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'subject_id' => $english->id,
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'ca1' => 15,
            'ca2' => 16,
            'exam' => 50,
        ]);

        ResultPublication::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'published_at' => now(),
            'published_by' => 1,
        ]);

        $url = route('whatsapp.report-card', [
            'studentId' => $student->id,
            'term' => 1,
            'session' => '2025/2026',
            'key' => 'test-api-key',
        ]);

        $response = $this->get($url);
        $response->assertOk();

        $pdfPath = storage_path('app/test-report-card-compact.pdf');
        file_put_contents($pdfPath, (string) $response->getContent());

        $textPath = storage_path('app/test-report-card-compact.txt');
        exec("pdftotext {$pdfPath} {$textPath}");
        $text = file_exists($textPath) ? file_get_contents($textPath) : '';

        $this->assertStringContainsString('Mathematics', $text, 'Compact template missing Mathematics. Text: ' . substr($text, 0, 800));
        $this->assertStringContainsString('English', $text, 'Compact template missing English. Text: ' . substr($text, 0, 800));

        // Ensure no row duplication in the compact template either.
        $this->assertSame(1, substr_count($text, 'Mathematics'), 'Mathematics duplicated in compact PDF');
        $this->assertSame(1, substr_count($text, 'English'), 'English duplicated in compact PDF');
    }

    public function test_whatsapp_report_card_uses_score_class_when_student_moved(): void
    {
        config(['services.whatsapp.api_key' => 'test-api-key']);

        $tenant = $this->createTenantWithTemplate('compact');

        $oldClass = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'JSS 1A',
            'level' => 1,
        ]);
        $newClass = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'JSS 2A',
            'level' => 2,
        ]);
        $section = Section::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $oldClass->id,
            'name' => 'A',
        ]);

        // Student has been moved to a new class, but scores are under the old class.
        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-MOVE',
            'first_name' => 'Moved',
            'last_name' => 'Student',
            'class_id' => $newClass->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $maths = Subject::query()->create(['tenant_id' => $tenant->id, 'name' => 'Mathematics', 'code' => 'MTH']);

        SubjectAllocation::query()->create([
            'tenant_id' => $tenant->id,
            'teacher_id' => User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'teacher'])->id,
            'subject_id' => $maths->id,
            'class_id' => $oldClass->id,
        ]);

        Score::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'subject_id' => $maths->id,
            'class_id' => $oldClass->id,
            'term' => 1,
            'session' => '2025/2026',
            'ca1' => 18,
            'ca2' => 17,
            'exam' => 56,
        ]);

        ResultPublication::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $oldClass->id,
            'term' => 1,
            'session' => '2025/2026',
            'published_at' => now(),
            'published_by' => 1,
        ]);

        $serviceData = app(\App\Support\ReportCardService::class)->build($student, 1, '2025/2026');
        $this->assertCount(1, $serviceData['rows']);
        $this->assertSame(91, $serviceData['rows']->first()['total']);

        $url = route('whatsapp.report-card', [
            'studentId' => $student->id,
            'term' => 1,
            'session' => '2025/2026',
            'key' => 'test-api-key',
        ]);

        $response = $this->get($url);
        $response->assertOk();

        $pdfPath = storage_path('app/test-report-card-moved.pdf');
        file_put_contents($pdfPath, (string) $response->getContent());

        $textPath = storage_path('app/test-report-card-moved.txt');
        exec("pdftotext {$pdfPath} {$textPath}");
        $text = file_exists($textPath) ? file_get_contents($textPath) : '';

        $this->assertStringContainsString('Mathematics', $text, 'Scores under old class not found. Text: ' . substr($text, 0, 800));
        $this->assertStringContainsString('91', $text, 'Score total missing after class move');
    }

    private function createTenantWithTemplate(string $template): Tenant
    {
        $tenant = Tenant::query()->create([
            'slug' => 'test-school',
            'name' => 'Test School',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 500,
            'max_teachers' => 50,
        ]);

        app()->instance('currentTenant', $tenant);

        $settingsPath = storage_path('app/academyhub/tenants/' . $tenant->id . '/settings.json');
        File::ensureDirectoryExists(dirname($settingsPath));
        File::put($settingsPath, json_encode([
            'school_name' => 'Test School',
            'report_card_template' => $template,
            'rc_show_position' => true,
            'rc_show_attendance' => true,
            'rc_show_grading_key' => true,
            'rc_show_class_average' => true,
            'rc_show_watermark' => false,
            'rc_show_next_term_date' => false,
            'rc_show_teacher_remarks' => false,
            'rc_show_principal_remarks' => false,
        ]));

        return $tenant;
    }

    public function test_whatsapp_report_card_uses_selected_template_and_contains_scores(): void
    {
        config(['services.whatsapp.api_key' => 'test-api-key']);

        $tenant = $this->createTenantWithTemplate('greenwood');

        $class = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'JSS 1A',
            'level' => 1,
        ]);
        $section = Section::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'A',
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $maths = Subject::query()->create(['tenant_id' => $tenant->id, 'name' => 'Mathematics', 'code' => 'MTH']);
        $english = Subject::query()->create(['tenant_id' => $tenant->id, 'name' => 'English', 'code' => 'ENG']);

        SubjectAllocation::query()->create([
            'tenant_id' => $tenant->id,
            'teacher_id' => User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'teacher'])->id,
            'subject_id' => $maths->id,
            'class_id' => $class->id,
        ]);
        SubjectAllocation::query()->create([
            'tenant_id' => $tenant->id,
            'teacher_id' => User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'teacher'])->id,
            'subject_id' => $english->id,
            'class_id' => $class->id,
        ]);

        Score::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'subject_id' => $maths->id,
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'ca1' => 18,
            'ca2' => 17,
            'exam' => 56,
        ]);
        Score::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'subject_id' => $english->id,
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'ca1' => 15,
            'ca2' => 16,
            'exam' => 50,
        ]);

        ResultPublication::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'published_at' => now(),
            'published_by' => 1,
        ]);

        // Verify the service builds rows with subjects and scores before PDF rendering.
        $serviceData = app(\App\Support\ReportCardService::class)->build($student, 1, '2025/2026');
        $this->assertCount(2, $serviceData['rows'], 'Expected 2 subject rows from service');
        $rowNames = $serviceData['rows']->pluck('subject.name')->all();
        $this->assertContains('Mathematics', $rowNames);
        $this->assertContains('English', $rowNames);
        $mathsRow = $serviceData['rows']->first(fn ($r) => $r['subject']->name === 'Mathematics');
        $this->assertSame(91, $mathsRow['total']);

        $url = route('whatsapp.report-card', [
            'studentId' => $student->id,
            'term' => 1,
            'session' => '2025/2026',
            'key' => 'test-api-key',
        ]);

        $response = $this->get($url);
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = (string) $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);

        // Save PDF for inspection and extract text for assertions.
        $pdfPath = storage_path('app/test-report-card.pdf');
        file_put_contents($pdfPath, $content);

        $textPath = storage_path('app/test-report-card.txt');
        exec("pdftotext {$pdfPath} {$textPath}");
        $text = file_exists($textPath) ? file_get_contents($textPath) : '';

        // The selected Greenwood template should mention the school name and subjects with scores.
        $this->assertStringContainsString('TEST SCHOOL', $text, 'School name from tenant settings missing. PDF text: ' . substr($text, 0, 500));
        $this->assertStringContainsString('Mathematics', $text, 'Mathematics subject missing. PDF text: ' . substr($text, 0, 500));
        $this->assertStringContainsString('English', $text, 'English subject missing. PDF text: ' . substr($text, 0, 500));
        $this->assertStringContainsString('91', $text, 'Mathematics total missing');
        $this->assertStringContainsString('81', $text, 'English total missing');

        // Ensure no row duplication: each subject should appear exactly once in extracted text.
        $this->assertSame(1, substr_count($text, 'Mathematics'), 'Mathematics subject duplicated in PDF');
        $this->assertSame(1, substr_count($text, 'English'), 'English subject duplicated in PDF');
    }
}
