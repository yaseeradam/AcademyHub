<?php

namespace App\Livewire\Homework;

use App\Models\Homework;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Homework')]
class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $homeworkId;
    public $isFormattingWithAI = false;
    
    public $search = '';

    public $class_id = '';
    public $section_id = '';
    public $subject_id = '';
    public $title = '';
    public $content = '';
    public $due_date = '';

    public function render()
    {
        $query = Homework::with(['class', 'section', 'subject', 'submissions'])
            ->where('teacher_id', auth()->id());

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('class', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('subject', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        $homework = $query->latest()
            ->paginate(10);

        $user = auth()->user();

        $classIds = $user->role === 'admin'
            ? null
            : \App\Models\SubjectAllocation::where('teacher_id', $user->id)->pluck('class_id')->unique();

        $classes = $classIds === null
            ? SchoolClass::orderBy('name')->get()
            : SchoolClass::whereIn('id', $classIds)->orderBy('name')->get();

        $subjects = $classIds === null
            ? Subject::orderBy('name')->get()
            : Subject::whereIn('id',
                \App\Models\SubjectAllocation::where('teacher_id', $user->id)
                    ->when($this->class_id, fn($q) => $q->where('class_id', $this->class_id))
                    ->pluck('subject_id')->unique()
              )->orderBy('name')->get();

        return view('livewire.homework.index', [
            'homework' => $homework,
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }

    public function getSectionsProperty()
    {
        if (!$this->class_id) {
            return collect();
        }
        return Section::where('class_id', $this->class_id)->orderBy('name')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedClassId()
    {
        $this->section_id = '';
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $homework = Homework::findOrFail($id);
        
        if ($homework->due_date < now()->startOfDay()) {
            session()->flash('error', 'Cannot edit a homework assignment that has passed its due date.');
            return;
        }

        $this->homeworkId = $homework->id;
        $this->class_id = $homework->class_id;
        $this->section_id = $homework->section_id;
        $this->subject_id = $homework->subject_id;
        $this->title = $homework->title;
        $this->content = $homework->content;
        $this->due_date = $homework->due_date->format('Y-m-d');
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'due_date'   => 'required|date',
        ]);

        $user = auth()->user();
        if ($user->role !== 'admin') {
            $allowed = \App\Models\SubjectAllocation::where('teacher_id', $user->id)
                ->where('class_id', $this->class_id)
                ->exists();
            abort_unless($allowed, 403, 'You are not assigned to this class.');
        }

        $data = [
            'teacher_id' => auth()->id(),
            'class_id' => $this->class_id,
            'section_id' => $this->section_id ?: null,
            'subject_id' => $this->subject_id,
            'title' => $this->title,
            'content' => $this->content,
            'due_date' => $this->due_date,
        ];

        if ($this->editMode) {
            Homework::findOrFail($this->homeworkId)->update($data);
            session()->flash('message', 'Homework updated successfully.');
        } else {
            $hw = Homework::create($data);
            session()->flash('message', 'Homework created successfully.');

            // Notify all students in the class
            $students = \App\Models\Student::query()
                ->where('class_id', $hw->class_id)
                ->where('status', 'Active')
                ->when($hw->section_id, fn($q) => $q->where('section_id', $hw->section_id))
                ->pluck('id');

            $subject = \App\Models\Subject::find($hw->subject_id)?->name ?? 'a subject';
            foreach ($students as $studentId) {
                \App\Models\StudentNotification::send(
                    $studentId,
                    'New Homework: ' . $hw->title,
                    "New {$subject} homework assigned. Due: " . $hw->due_date->format('M d, Y'),
                    'homework',
                    route('student.homework')
                );
            }
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Homework::findOrFail($id)->delete();
        session()->flash('message', 'Homework deleted successfully.');
    }

    public function formatWithAI()
    {
        if (empty($this->content)) {
            $this->addError('content', 'Please enter some content first.');
            return;
        }

        $prompt = "Format this homework assignment into a well-structured document. Use clear section titles, numbered lists (1. 2. 3.), and proper spacing. Make it professional and easy to read.\n\nIMPORTANT: Write in plain text format without any markdown symbols (no #, *, **, -, etc.). Use simple formatting that's ready to copy-paste.\n\nContent to format:\n\n" . $this->content;

        // Try Gemini first, then fallback to Groq
        $formattedContent = $this->tryGeminiAPI($prompt) ?? $this->tryGroqAPI($prompt);

        if ($formattedContent) {
            // Remove any remaining markdown symbols
            $formattedContent = $this->cleanMarkdown($formattedContent);
            $this->content = $formattedContent;
            $this->dispatch('content-formatted', content: $formattedContent);
            
            \Illuminate\Support\Facades\Log::info('AI formatting successful', [
                'user_id' => auth()->id(),
                'content_length' => strlen($this->content)
            ]);
        } else {
            $this->addError('content', 'AI service is currently unavailable. Please format manually.');
        }
    }

    public function generateWithAI()
    {
        $topic = trim($this->title ?? '');
        
        if (empty($topic)) {
            session()->flash('error', 'Please enter a topic in the Title field first (e.g., "Photosynthesis" or "World War 2"), then click Generate Assignment.');
            return;
        }

        $subjectName = $this->subject_id ? Subject::find($this->subject_id)?->name : 'the subject';
        $className = $this->class_id ? SchoolClass::find($this->class_id)?->name : 'students';

        $prompt = "Create a homework assignment for {$className} about: {$topic}\n\nSubject: {$subjectName}\n\nGenerate a complete homework assignment with:\n- Learning objectives\n- Clear instructions\n- Specific tasks or questions\n- Submission requirements\n\nIMPORTANT: Write in plain text format without any markdown symbols (no #, *, **, -, etc.). Use clear section titles, numbered lists (1. 2. 3.), and proper spacing. Make it ready to copy-paste directly.";

        // Try Gemini first, then fallback to Groq
        $generatedContent = $this->tryGeminiAPI($prompt) ?? $this->tryGroqAPI($prompt);

        if ($generatedContent) {
            // Remove any remaining markdown symbols
            $generatedContent = $this->cleanMarkdown($generatedContent);
            $this->content = $generatedContent;
            $this->dispatch('content-generated', content: $generatedContent);
            
            \Illuminate\Support\Facades\Log::info('AI generation successful', [
                'user_id' => auth()->id(),
                'topic' => $topic,
                'class' => $className,
                'subject' => $subjectName,
                'content_length' => strlen($generatedContent)
            ]);
        } else {
            session()->flash('error', 'AI service is currently unavailable. Please create homework manually.');
        }
    }

    private function cleanMarkdown($text)
    {
        // Remove markdown headers (# ## ###)
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        
        // Remove bold (**text** or __text__)
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/__(.+?)__/s', '$1', $text);
        
        // Remove italic (*text* or _text_)
        $text = preg_replace('/\*(.+?)\*/s', '$1', $text);
        $text = preg_replace('/_(.+?)_/s', '$1', $text);
        
        // Remove bullet points (- or *)
        $text = preg_replace('/^[\*\-]\s+/m', '', $text);
        
        // Remove code blocks (```)
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        $text = preg_replace('/`(.+?)`/', '$1', $text);
        
        return trim($text);
    }

    private function tryGeminiAPI($prompt)
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            if (empty($apiKey)) {
                \Illuminate\Support\Facades\Log::warning('Gemini API key not configured');
                return null;
            }
            
            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($content) {
                    \Illuminate\Support\Facades\Log::info('Gemini API success');
                    return $content;
                }
            }
            
            \Illuminate\Support\Facades\Log::warning('Gemini API failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gemini API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function tryGroqAPI($prompt)
    {
        try {
            $apiKey = env('GROQ_API_KEY');
            if (empty($apiKey)) {
                \Illuminate\Support\Facades\Log::warning('Groq API key not configured');
                return null;
            }
            
            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an experienced teacher creating homework assignments. Generate a complete, well-structured homework assignment with clear objectives, instructions, questions/tasks, and submission guidelines. Make it age-appropriate, engaging, and educational. Use proper formatting with headings, bullet points, and numbered lists.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;
                
                if ($content) {
                    \Illuminate\Support\Facades\Log::info('Groq API success (fallback)');
                    return $content;
                }
            }
            
            \Illuminate\Support\Facades\Log::error('Groq API failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Groq API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function applyAIContent($content)
    {
        $this->content = $content;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['homeworkId', 'class_id', 'section_id', 'subject_id', 'title', 'content', 'due_date']);
        $this->editMode = false;
    }
}
