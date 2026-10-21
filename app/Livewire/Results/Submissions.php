<?php

namespace App\Livewire\Results;

use App\Models\InAppNotification;
use App\Models\ScoreSubmission;
use App\Support\Audit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Score Submissions')]
class Submissions extends Component
{
    public string $statusFilter = 'submitted';

    public function approve($id)
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.review'), 403);

        $submission = ScoreSubmission::findOrFail($id);
        $submission->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
            'note' => null,
        ]);

        Audit::log('results.submission_approved', $submission, [
            'reviewed_by' => $user->id,
        ]);

        InAppNotification::query()->create([
            'user_id' => $submission->teacher_id,
            'title' => 'Score submission approved',
            'body' => "Your score submission was approved ({$submission->session} T{$submission->term}).",
            'link' => '/results/entry?' . http_build_query([
                'class' => $submission->class_id,
                'subject' => $submission->subject_id,
                'term' => $submission->term,
                'session' => $submission->session,
            ]),
        ]);

        $this->dispatch('submission-approved');
        $this->dispatch('alert', message: 'Submission approved successfully.', type: 'success');
    }

    public function reject($id, $note = null)
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.review'), 403);

        $submission = ScoreSubmission::findOrFail($id);
        $cleanNote = trim((string) $note);
        $submission->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
            'note' => $cleanNote ?: null,
        ]);

        Audit::log('results.submission_rejected', $submission, [
            'reviewed_by' => $user->id,
            'note' => $cleanNote ?: null,
        ]);

        InAppNotification::query()->create([
            'user_id' => $submission->teacher_id,
            'title' => 'Score submission rejected',
            'body' => "Your score submission was rejected" . ($cleanNote ? ": {$cleanNote}" : '.'),
            'link' => '/results/entry?' . http_build_query([
                'class' => $submission->class_id,
                'subject' => $submission->subject_id,
                'term' => $submission->term,
                'session' => $submission->session,
            ]),
        ]);

        $this->dispatch('submission-rejected');
        $this->dispatch('alert', message: 'Submission returned for revision.', type: 'warning');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.review'), 403);

        $submissions = ScoreSubmission::query()
            ->with(['teacher', 'schoolClass', 'subject', 'approver'])
            ->when($this->statusFilter, function ($q) {
                if ($this->statusFilter === 'pending' || $this->statusFilter === 'submitted') {
                    $q->whereIn('status', ['submitted', 'pending']);
                } else {
                    $q->where('status', $this->statusFilter);
                }
            })
            ->orderByDesc('submitted_at')
            ->limit(100)
            ->get();

        return view('livewire.results.submissions', compact('submissions'));
    }
}
