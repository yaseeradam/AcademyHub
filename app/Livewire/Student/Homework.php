<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\HomeworkSubmission;
use Livewire\Component;
use Livewire\WithFileUploads;

class Homework extends Component
{
    use WithFileUploads;

    public $student;
    public $homework;
    public $selectedHomework;
    public $submission = '';
    public $attachment;
    public $filter = 'pending';

    public function mount()
    {
        $studentId = session('student_id');
        if (!$studentId) {
            return redirect()->route('login');
        }

        $this->student = Student::find($studentId);
        $this->loadHomework();
    }

    public function loadHomework()
    {
        $allHomework = $this->student->getHomeworkForStudent();

        $this->homework = match($this->filter) {
            'pending' => $allHomework->filter(fn($hw) => $hw->submissions->isEmpty() && $hw->due_date >= now()),
            'overdue' => $allHomework->filter(fn($hw) => $hw->submissions->isEmpty() && $hw->due_date < now()),
            'submitted' => $allHomework->filter(fn($hw) => $hw->submissions->isNotEmpty()),
            default => $allHomework,
        };
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->loadHomework();
    }

    public function selectHomework($homeworkId)
    {
        $this->selectedHomework = $this->student->getHomeworkForStudent()
            ->firstWhere('id', $homeworkId);
        $this->submission = '';
        $this->attachment = null;
    }

    public function submitHomework()
    {
        $this->validate([
            'submission' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('homework-submissions', 'public');
        }

        HomeworkSubmission::create([
            'homework_id' => $this->selectedHomework->id,
            'student_id' => $this->student->id,
            'submission' => $this->submission,
            'attachment' => $attachmentPath,
            'submitted_at' => now(),
        ]);

        session()->flash('success', 'Homework submitted successfully!');
        $this->selectedHomework = null;
        $this->submission = '';
        $this->attachment = null;
        $this->loadHomework();
    }

    public function render()
    {
        return view('livewire.student.homework')
            ->layout('layouts.student');
    }
}
