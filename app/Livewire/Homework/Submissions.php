<?php

namespace App\Livewire\Homework;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\StudentNotification;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Homework Submissions')]
class Submissions extends Component
{
    public $homework;
    public $showGradeModal = false;
    public $submissionId;
    public $grade;
    public $feedback;

    public function mount($id)
    {
        $this->homework = Homework::with(['class', 'section', 'subject'])->findOrFail($id);
    }

    public function render()
    {
        $submissions = HomeworkSubmission::with('student')
            ->where('homework_id', $this->homework->id)
            ->latest('submitted_at')
            ->get();

        return view('livewire.homework.submissions', [
            'submissions' => $submissions,
        ]);
    }

    public function gradeSubmission($id)
    {
        $submission = HomeworkSubmission::findOrFail($id);
        $this->submissionId = $submission->id;
        $this->grade = $submission->grade;
        $this->feedback = $submission->feedback;
        $this->showGradeModal = true;
    }

    public function saveGrade()
    {
        $this->validate([
            'grade'    => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        HomeworkSubmission::findOrFail($this->submissionId)->update([
            'grade'     => $this->grade,
            'feedback'  => $this->feedback,
            'graded_at' => now(),
        ]);

        $submission = HomeworkSubmission::with('homework')->find($this->submissionId);
        if ($submission) {
            StudentNotification::send(
                $submission->student_id,
                'Homework Graded: ' . ($submission->homework->title ?? 'Assignment'),
                $this->grade !== null
                    ? "You scored {$this->grade}/100." . ($this->feedback ? ' Teacher: ' . Str::limit($this->feedback, 80) : '')
                    : ($this->feedback ?? ''),
                'grade',
                route('student.homework')
            );
        }

        $this->closeGradeModal();
        $this->dispatch('alert', message: 'Grade saved successfully.', type: 'success');
    }

    public function closeGradeModal()
    {
        $this->showGradeModal = false;
        $this->reset(['submissionId', 'grade', 'feedback']);
    }
}
