<?php

namespace App\Livewire\ELearning;

use App\Models\ClassNote;
use App\Models\SchoolClass;
use App\Models\Subject;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
#[Title('E-Learning Notes Hub')]
class Index extends Component
{
    use WithFileUploads;

    // Filter/Search State
    public $search = '';
    public $selectedClass = '';
    public $selectedSubject = '';
    public $selectedTerm = '';

    // Form State
    public $title = '';
    public $description = '';
    public $class_id = '';
    public $subject_id = '';
    public $term_name = 'First Term';
    public $file; // for real file uploads

    // Edit/Delete State
    public $confirmingDeleteId = null;
    public $showCreateModal = false;

    protected $rules = [
        'title' => 'required|string|max:100',
        'class_id' => 'required|exists:classes,id',
        'subject_id' => 'required|exists:subjects,id',
        'term_name' => 'required|string|in:First Term,Second Term,Third Term',
        'description' => 'nullable|string|max:500',
        'file' => 'required|file|max:10240', // Max 10MB
    ];

    public function mount()
    {
        // Pre-fill if needed
    }

    public function saveNote()
    {
        $this->validate();

        $originalName = $this->file->getClientOriginalName();
        $fileSize = $this->formatBytes($this->file->getSize());
        
        // Save file in private/secure disk or public
        $path = $this->file->store('class-notes', 'public');

        ClassNote::create([
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'user_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'term_name' => $this->term_name,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'downloads' => 0,
        ]);

        $this->reset(['title', 'description', 'class_id', 'subject_id', 'file']);
        $this->showCreateModal = false;
        
        $this->dispatch('alert', message: 'Class note uploaded successfully!', type: 'success');
    }

    public function triggerDelete($id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDeleteId = null;
    }

    public function deleteNote()
    {
        if ($this->confirmingDeleteId) {
            $note = ClassNote::findOrFail($this->confirmingDeleteId);
            
            // Delete file from disk
            if ($note->file_path) {
                Storage::disk('public')->delete($note->file_path);
            }
            
            $note->delete();
            $this->confirmingDeleteId = null;
            $this->dispatch('alert', message: 'Class note deleted successfully.', type: 'success');
        }
    }

    public function downloadNote($id)
    {
        $note = ClassNote::findOrFail($id);
        $note->increment('downloads');
        return Storage::disk('public')->download($note->file_path, $note->file_name);
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function render()
    {
        $query = ClassNote::with(['schoolClass', 'subject', 'user']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedClass)) {
            $query->where('class_id', $this->selectedClass);
        }

        if (!empty($this->selectedSubject)) {
            $query->where('subject_id', $this->selectedSubject);
        }

        if (!empty($this->selectedTerm)) {
            $query->where('term_name', $this->selectedTerm);
        }

        $notes = $query->latest()->get();

        // Get filter options
        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('livewire.e-learning.index', [
            'notes' => $notes,
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }
}
