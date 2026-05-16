<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\HomeworkSubmission;
use App\Models\InAppNotification;
use Livewire\Component;
use Livewire\WithFileUploads;

class Homework extends Component
{
    use WithFileUploads;

    public $student;
    public $homework;
    public $selectedHomeworkId = null;
    public $submission = '';
    public $attachment;
    public $filter = 'pending';
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => 'pending']
    ];

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

        if ($this->search) {
            $allHomework = $allHomework->filter(fn($hw) => 
                str_contains(strtolower($hw->title), strtolower($this->search)) ||
                str_contains(strtolower($hw->content), strtolower($this->search)) ||
                str_contains(strtolower($hw->subject->name), strtolower($this->search))
            );
        }

        $this->homework = match($this->filter) {
            'pending'   => $allHomework->filter(fn($hw) => $hw->submissions->isEmpty() && $hw->due_date >= now()),
            'overdue'   => $allHomework->filter(fn($hw) => $hw->submissions->isEmpty() && $hw->due_date < now()),
            'submitted' => $allHomework->filter(fn($hw) => $hw->submissions->isNotEmpty()),
            default     => $allHomework,
        };
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->loadHomework();
    }

    public function updatedSearch()
    {
        $this->loadHomework();
    }

    public function selectHomework($homeworkId)
    {
        $this->selectedHomeworkId = $homeworkId;
        $this->submission = '';
        $this->attachment = null;
    }

    public function closePanel(): void
    {
        $this->selectedHomeworkId = null;
        $this->submission = '';
        $this->attachment = null;
        $this->loadHomework();
    }
    public function getSelectedHomeworkProperty()
    {
        if (! $this->selectedHomeworkId) return null;

        return \App\Models\Homework::where('id', $this->selectedHomeworkId)
            ->where('class_id', $this->student->class_id)
            ->with(['subject', 'teacher', 'submissions' => fn($q) => $q->where('student_id', $this->student->id)])
            ->first();
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

        $hw = $this->selectedHomework;

        HomeworkSubmission::create([
            'homework_id' => $hw->id,
            'student_id'  => $this->student->id,
            'submission'  => $this->submission,
            'attachment'  => $attachmentPath,
            'submitted_at' => now(),
        ]);

        InAppNotification::create([
            'user_id' => $hw->teacher_id,
            'title'   => 'Homework Submission',
            'body'    => $this->student->full_name . ' submitted "' . $hw->title . '" (' . $hw->subject->name . ')',
            'link'    => route('homework.index'),
        ]);

        session()->flash('success', 'Homework submitted successfully!');
        $this->closePanel();
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
