<?php

namespace App\Livewire\Results;

use App\Models\ScoreSubmission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Score Submissions')]
class Submissions extends Component
{
    public string $statusFilter = 'pending';

    public function approve($id)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $submission = ScoreSubmission::findOrFail($id);
        $submission->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $this->dispatch('submission-approved');
    }

    public function reject($id, $note = null)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $submission = ScoreSubmission::findOrFail($id);
        $submission->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'note' => $note,
        ]);

        $this->dispatch('submission-rejected');
    }

    public function render()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $submissions = ScoreSubmission::query()
            ->with(['teacher', 'schoolClass', 'subject', 'approver'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('submitted_at')
            ->limit(100)
            ->get();

        return view('livewire.results.submissions', compact('submissions'));
    }
}
