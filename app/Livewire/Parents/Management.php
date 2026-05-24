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
    public bool $showEditWhatsappModal = false;
    public ?int $selectedParentId = null;
    public ?int $editParentId = null;

    // Create parent form
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $whatsappPhone = '';

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

    public function openEditWhatsappModal(int $parentId): void
    {
        $parent = User::query()->where('role', 'parent')->whereKey($parentId)->first();
        if (!$parent) {
            return;
        }

        $this->editParentId = $parent->id;
        $this->whatsappPhone = $parent->whatsapp_phone ?? '';
        $this->showEditWhatsappModal = true;
    }

    public function closeEditWhatsappModal(): void
    {
        $this->showEditWhatsappModal = false;
        $this->editParentId = null;
        $this->whatsappPhone = '';
    }

    public function updateWhatsappPhone(): void
    {
        if (!$this->editParentId) {
            return;
        }

        $this->validate([
            'whatsappPhone' => 'nullable|string|max:20',
        ]);

        $parent = User::query()->where('role', 'parent')->whereKey($this->editParentId)->first();
        if (!$parent) {
            return;
        }

        $normalized = preg_replace('/\D+/', '', $this->whatsappPhone);
        $normalized = $normalized !== '' ? $normalized : null;

        $parent->whatsapp_phone = $normalized;
        $parent->whatsapp_verified = (bool) $normalized;
        $parent->whatsapp_subscribed = (bool) $normalized;
        $parent->save();

        Audit::log('parents.whatsapp_updated', $parent, [
            'parent_name' => $parent->name,
            'whatsapp_phone' => $parent->whatsapp_phone,
        ]);

        $this->dispatch('alert', message: 'WhatsApp number updated.', type: 'success');
        $this->dispatch('refresh');
        $this->closeEditWhatsappModal();
    }

    public function syncWhatsappPhones(): void
    {
        $updated = 0;
        $parents = User::query()->where('role', 'parent')->get();

        foreach ($parents as $parent) {
            if (!empty($parent->whatsapp_phone)) {
                continue;
            }

            $phone = $parent->custom_fields['phone'] ?? null;
            if (!$phone) {
                continue;
            }

            $normalized = preg_replace('/\D+/', '', (string) $phone);
            if ($normalized === '') {
                continue;
            }

            $parent->whatsapp_phone = $normalized;
            $parent->whatsapp_verified = true;
            $parent->whatsapp_subscribed = true;
            $parent->save();
            $updated++;
        }

        $this->dispatch('alert', message: "WhatsApp sync complete. Updated {$updated} parent(s).", type: 'success');
        $this->dispatch('refresh');
    }

    public function createParent(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users', 'email')->where('tenant_id', \App\Support\TenantSettings::tenantId())],
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        $normalizedPhone = preg_replace('/\D+/', '', $this->phone);
        $normalizedPhone = $normalizedPhone !== '' ? $normalizedPhone : null;

        $parent = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'parent',
            'is_active' => true,
            'custom_fields' => $this->phone ? ['phone' => $this->phone] : null,
            'whatsapp_phone' => $normalizedPhone,
            'whatsapp_verified' => (bool) $normalizedPhone,
            'whatsapp_subscribed' => (bool) $normalizedPhone,
        ]);

        Audit::log('parents.created', $parent, [
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

        Audit::log('parents.children_linked', $this->selectedParent, [
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

        Audit::log('parents.deleted', $parent, [
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
        $this->whatsappPhone = '';
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'admin', 403);

        return view('livewire.parents.management');
    }
}
