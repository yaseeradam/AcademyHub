<?php

namespace App\Livewire\Student;

use App\Models\StudentNotification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    #[Computed]
    public function unreadCount(): int
    {
        $studentId = session('student_id');
        if (! $studentId) return 0;

        return (int) StudentNotification::where('student_id', $studentId)
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function notifications()
    {
        $studentId = session('student_id');
        if (! $studentId) return collect();

        return StudentNotification::where('student_id', $studentId)
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('id')
            ->limit(30)
            ->get();
    }

    public function markRead(int $id): void
    {
        $studentId = session('student_id');
        if (! $studentId) return;

        StudentNotification::where('id', $id)
            ->where('student_id', $studentId)
            ->update(['read_at' => now()]);

        unset($this->unreadCount, $this->notifications);
    }

    public function markAllRead(): void
    {
        $studentId = session('student_id');
        if (! $studentId) return;

        StudentNotification::where('student_id', $studentId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        unset($this->unreadCount, $this->notifications);
    }

    public function render()
    {
        return view('livewire.student.notification-bell');
    }
}
