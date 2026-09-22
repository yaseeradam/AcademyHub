<?php

namespace App\Livewire\Student;

use App\Models\StudentNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.student')]
#[Title('Notifications')]
class Notifications extends Component
{
    public function markAllRead(): void
    {
        $studentId = session('student_id');
        if (! $studentId) return;

        StudentNotification::query()
            ->where('student_id', $studentId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markRead(int $id): void
    {
        $studentId = session('student_id');
        if (! $studentId) return;

        StudentNotification::query()
            ->where('id', $id)
            ->where('student_id', $studentId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function delete(int $id): void
    {
        $studentId = session('student_id');
        if (! $studentId) return;

        StudentNotification::query()
            ->where('id', $id)
            ->where('student_id', $studentId)
            ->delete();
    }

    public function render()
    {
        $studentId = session('student_id');

        $notifications = $studentId
            ? StudentNotification::query()
                ->where('student_id', $studentId)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.student.notifications', compact('notifications'));
    }
}
