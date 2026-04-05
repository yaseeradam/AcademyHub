<?php

namespace App\Livewire\Parents;

use App\Models\User;
use App\Models\Student;
use App\Support\Audit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Parent Management')]
class Management extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;
    public bool $showLinkModal = false;
    public ?int $selectedParentId = null;

    // Create parent form
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';

    // Link children form
    public array $selectedChildren = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function parents(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::query()
            ->where('role', 'parent')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('students')
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function availableStudents(): Collection
    {
        return Student::query()
            ->with(['schoolClass', 'section'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function selectedParent(): ?User
    {
        if (!$this->selectedParentId) {
            return null;
        }

        return User::with('students.schoolClass')->find($this->selectedParentId);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openLinkModal(int $parentId): void
    {
        $this->selectedParentId = $parentId;
        $this->selectedChildren = $this->selectedParent?->students->pluck('id')->toArray() ?? [];
        $this->showLinkModal = true;
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal = false;
        $this->selectedParentId = null;
        $this->selectedChildren = [];
    }

    public function createParent(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        $parent = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'parent',
            'is_active' => true,
            'custom_fields' => $this->phone ? ['phone' => $this->phone] : null,
        ]);

        Audit::log('parents.created', $parent->id, [
            'name' => $parent->name,
            'email' => $parent->email,
        ]);

        $this->dispatch('alert', message: 'Parent account created successfully.', type: 'success');
        $this->dispatch('refresh');
        $this->closeCreateModal();
    }

    public function linkChildren(): void
    {
        if (!$this->selectedParent) {
            return;
        }

        $this->selectedParent->students()->sync($this->selectedChildren);

        Audit::log('parents.children_linked', $this->selectedParent->id, [
            'parent_name' => $this->selectedParent->name,
            'children_count' => count($this->selectedChildren),
            'children_ids' => $this->selectedChildren,
        ]);

        $this->dispatch('alert', message: 'Children linked successfully.', type: 'success');
        $this->dispatch('refresh');
        $this->closeLinkModal();
    }

    public function deleteParent(int $parentId): void
    {
        $parent = User::find($parentId);
        if (!$parent || $parent->role !== 'parent') {
            return;
        }

        $parent->students()->detach();
        $parent->delete();

        Audit::log('parents.deleted', $parentId, [
            'name' => $parent->name,
            'email' => $parent->email,
        ]);

        $this->dispatch('alert', message: 'Parent account deleted successfully.', type: 'success');
        $this->dispatch('refresh');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->phone = '';
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'admin', 403);

        return view('livewire.parents.management');
    }
}