<?php

namespace Tests\Feature;

use App\Livewire\Imports\Students as ImportsStudents;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ImportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_students_from_csv(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $csv = implode("\n", [
            'admission_number,first_name,last_name,gender,class_name,section_name,status',
            'ADM-2026-0100,John,Doe,Male,JSS 2,A,Active',
            '',
        ]);

        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

        Livewire::actingAs($admin)
            ->test(ImportsStudents::class)
            ->set('file', $file)
            ->call('analyze')
            ->call('import');

        $this->assertDatabaseHas('students', [
            'admission_number' => 'ADM-2026-0100',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'Male',
            'status' => 'Active',
        ]);
    }
}

