<?php

namespace Tests\Feature;

use App\Livewire\Certificates\Index as CertificatesIndex;
use App\Models\Certificate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CertificatesCustomizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_defaults_can_be_customized_via_config(): void
    {
        $this->seed();

        config([
            'academyhub.certificate_default_type' => 'Character',
            'academyhub.certificate_default_title' => 'Testimonial',
            'academyhub.certificate_default_body' => 'Hello {student_name}',
        ]);

        $teacher = User::query()->where('email', 'teacher@academyhub.local')->firstOrFail();

        Livewire::actingAs($teacher)
            ->test(CertificatesIndex::class)
            ->assertSet('type', 'Character')
            ->assertSet('title', 'Testimonial')
            ->assertSet('body', 'Hello {student_name}');
    }

    public function test_certificate_pdf_can_be_downloaded(): void
    {
        $this->seed();

        config([
            'academyhub.certificate_orientation' => 'portrait',
            'academyhub.certificate_border_color' => '#ff0000',
            'academyhub.certificate_accent_color' => '#00ff00',
            'academyhub.certificate_show_logo' => false,
            'academyhub.certificate_show_watermark' => false,
            'academyhub.certificate_signature_label' => 'Principal',
            'academyhub.certificate_signature_name' => 'Jane Doe',
            'academyhub.certificate_template' => 'classic',
        ]);

        $teacher = User::query()->where('email', 'teacher@academyhub.local')->firstOrFail();
        $student = Student::query()->firstOrFail();

        $certificate = Certificate::query()->create([
            'student_id' => $student->id,
            'type' => 'General',
            'title' => 'Certificate',
            'body' => 'Test body for {student_name}',
            'issued_on' => '2026-02-07',
            'serial_number' => 'CERT-20260207-TEST01',
            'issued_by' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher)->get(route('certificates.download', $certificate));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_admin_can_preview_templates(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $cert = $this->actingAs($admin)->get(route('settings.templates.preview', [
            'type' => 'certificate',
            'template' => 'classic',
        ]));

        $cert->assertOk();
        $cert->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', (string) $cert->getContent());

        $report = $this->actingAs($admin)->get(route('settings.templates.preview', [
            'type' => 'report-card',
            'template' => 'compact',
        ]));

        $report->assertOk();
        $report->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', (string) $report->getContent());
    }
}
