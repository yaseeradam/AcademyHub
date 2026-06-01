<?php

namespace App\Livewire\Aptitude;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\AptitudeQuestion;
use App\Models\SchoolClass;

#[Layout('layouts.app')]
#[Title('Aptitude Test Questions Manager')]
class Questions extends Component
{
    public ?int $selectedClass = null;
    public string $question_text = '';
    public string $option_a = '';
    public string $option_b = '';
    public string $option_c = '';
    public string $option_d = '';
    public string $correct_option = 'A';
    public int $points = 10;

    protected $rules = [
        'selectedClass'   => 'required|exists:classes,id',
        'question_text'   => 'required|string',
        'option_a'        => 'required|string|max:255',
        'option_b'        => 'required|string|max:255',
        'option_c'        => 'required|string|max:255',
        'option_d'        => 'required|string|max:255',
        'correct_option'  => 'required|in:A,B,C,D',
        'points'          => 'required|integer|min:1|max:100',
    ];

    public function addQuestion(): void
    {
        $this->validate();

        $tenantId = auth()->user()->tenant_id;

        AptitudeQuestion::create([
            'tenant_id'     => $tenantId,
            'class_id'      => $this->selectedClass,
            'question_text' => $this->question_text,
            'option_a'      => $this->option_a,
            'option_b'      => $this->option_b,
            'option_c'      => $this->option_c,
            'option_d'      => $this->option_d,
            'correct_option'=> $this->correct_option,
            'points'        => $this->points,
        ]);

        $this->reset(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'points']);
        $this->dispatch('alert', message: 'Exam question added successfully!', type: 'success');
    }

    public function deleteQuestion(int $id): void
    {
        $question = AptitudeQuestion::findOrFail($id);
        $question->delete();

        $this->dispatch('alert', message: 'Question deleted successfully.', type: 'warning');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $classes = SchoolClass::where('tenant_id', $tenantId)->get();

        if (empty($this->selectedClass) && $classes->isNotEmpty()) {
            $this->selectedClass = $classes->first()->id;
        }

        $questions = AptitudeQuestion::where('tenant_id', $tenantId)
            ->where('class_id', $this->selectedClass)
            ->get();

        return view('livewire.aptitude.questions', compact('classes', 'questions'));
    }
}
