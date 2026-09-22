<?php

namespace App\Livewire\AuditLogs;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Audit Logs')]
class Index extends Component
{
    use WithPagination;

    public string $action = '';
    public ?int $userId = null;
    public ?string $from = null;
    public ?string $to = null;

    public function updatedAction(): void
    {
        $this->resetPage();
        unset($this->logs);
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
        unset($this->logs);
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
        unset($this->logs);
    }

    public function updatedTo(): void
    {
        $this->resetPage();
        unset($this->logs);
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email']);
    }

    #[Computed]
    public function logs()
    {
        $query = AuditLog::query()
            ->with('user:id,name,email,role')
            ->orderByDesc('id');

        $action = trim($this->action);
        if ($action !== '') {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query->paginate(25);
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('audit.view'), 403);

        return view('livewire.audit-logs.index');
    }
}

