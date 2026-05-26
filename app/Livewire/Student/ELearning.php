<?php

namespace App\Livewire\Student;

use App\Models\ClassNote;
use App\Models\Student;
use App\Models\Subject;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.student')]
#[Title('E-Learning Materials')]
class ELearning extends Component
{
    // Search and filters
    public $search = '';
    public $selectedSubject = '';
    public $selectedTerm = '';

    public function student()
    {
        $studentId = session('student_id');
        if ($studentId) {
            return Student::with(['schoolClass'])->find($studentId);
        }

        $user = auth()->user();
        if (!$user) return null;

        return Student::query()
            ->where('email', $user->email)
            ->orWhere('admission_number', $user->admission_number ?? '')
            ->first();
    }

    public function downloadNote($id)
    {
        $student = $this->student();
        abort_unless($student, 403);

        $note = ClassNote::where('class_id', $student->class_id)->findOrFail($id);
        $note->increment('downloads');
        return Storage::disk('public')->download($note->file_path, $note->file_name);
    }

    public function render()
    {
        $student = $this->student();
        if (!$student) {
            return view('livewire.student.e-learning', [
                'notes' => collect(),
                'subjects' => collect(),
            ]);
        }

        $query = ClassNote::with(['subject', 'user'])
            ->where('class_id', $student->class_id);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedSubject)) {
            $query->where('subject_id', $this->selectedSubject);
        }

        if (!empty($this->selectedTerm)) {
            $query->where('term_name', $this->selectedTerm);
        }

        $notes = $query->latest()->get();

        // Get filter subjects (only those that actually have notes shared with this class)
        $subjectIds = ClassNote::where('class_id', $student->class_id)
            ->pluck('subject_id')
            ->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        return view('livewire.student.e-learning', [
            'notes' => $notes,
            'subjects' => $subjects,
            'student' => $student,
        ]);
    }
}
