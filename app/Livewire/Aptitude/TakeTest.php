<?php

namespace App\Livewire\Aptitude;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Applicant;
use App\Models\AptitudeQuestion;

#[Layout('layouts.portal')] // Uses clean portal layout
#[Title('Admission Screening Examination')]
class TakeTest extends Component
{
    public Applicant $applicant;
    public $questions = [];
    public array $selectedAnswers = [];
    public bool $isFinished = false;
    public float $scorePercent = 0.0;
    public string $resultStatus = 'Pending';
    public bool $alreadyTaken = false;

    public function mount(Applicant $applicant): void
    {
        $this->applicant = $applicant;

        // Verify if test has already been taken
        if (in_array($applicant->status, ['Passed', 'Failed', 'Admitted'], true)) {
            $this->alreadyTaken = true;
            $this->scorePercent = (float) $applicant->test_score;
            $this->resultStatus = $applicant->status;
            $this->isFinished = true;
            return;
        }

        // Set tenant context dynamically
        app()->instance('currentTenant', $applicant->tenant);

        // Fetch questions for class
        $this->questions = AptitudeQuestion::where('tenant_id', $applicant->tenant_id)
            ->where('class_id', $applicant->class_id)
            ->get();
            
        if ($this->questions->isEmpty()) {
            $this->dispatch('alert', message: 'No questions set for this class level yet. Please alert the admission officer.', type: 'error');
        }

        // Pre-fill keys
        foreach ($this->questions as $q) {
            $this->selectedAnswers[$q->id] = '';
        }
    }

    public function submitTest(): void
    {
        if ($this->alreadyTaken || $this->isFinished) {
            return;
        }

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($this->questions as $q) {
            $totalPoints += $q->points;
            $userAns = $this->selectedAnswers[$q->id] ?? '';
            
            if (strtoupper(trim($userAns)) === strtoupper(trim($q->correct_option))) {
                $earnedPoints += $q->points;
            }
        }

        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0.0;
        $this->scorePercent = round($score, 2);

        $passed = $this->scorePercent >= 50.0;
        $this->resultStatus = $passed ? 'Passed' : 'Failed';

        // Update database
        $this->applicant->update([
            'test_score' => $this->scorePercent,
            'status'     => $this->resultStatus,
        ]);

        $this->isFinished = true;
    }

    public function render()
    {
        return view('livewire.aptitude.take-test')
            ->layout('layouts.portal');
    }
}
