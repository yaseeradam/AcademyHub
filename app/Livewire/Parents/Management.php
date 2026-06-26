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
use Illuminate\Validation\Rule;
use App\Support\TenantSettings;

#[Layout('layouts.app')]
#[Title('Parent Management')]
class Management extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;
    public bool $showLinkModal = false;
    public bool $showEditWhatsappModal = false;
    public bool $showEditModal = false;
    public ?int $selectedParentId = null;
    public ?int $editParentId = null;

    // Create parent form
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $whatsappPhone = '';

    // Edit parent form
    public string $editName = '';
    public string $editEmail = '';
    public string $editPhone = '';
    public string $editPassword = '';

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
            ->with(['students.schoolClass'])
            ->withCount('students')
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function parentBalances(): array
    {
        $parents = $this->parents();
        if ($parents->isEmpty()) {
            return [];
        }

        $studentIds = [];
        $classIds = [];
        $parentStudentsMap = [];

        foreach ($parents as $parent) {
            $parentStudentsMap[$parent->id] = [];
            foreach ($parent->students as $student) {
                $studentIds[] = $student->id;
                if ($student->class_id) {
                    $classIds[] = $student->class_id;
                }
                $parentStudentsMap[$parent->id][] = $student;
            }
        }

        $studentIds = array_unique($studentIds);
        $classIds = array_unique($classIds);

        if (empty($studentIds)) {
            return [];
        }

        $term = \App\Models\AcademicTerm::activeTermNumber();
        $session = \App\Models\AcademicTerm::activeSessionName();

        $feeRows = \App\Models\FeeStructure::query()
            ->whereIn('class_id', $classIds)
            ->where('category', 'Tuition')
            ->where(function ($q) use ($term) {
                if ($term === null) {
                    $q->whereNull('term');
                } else {
                    $q->whereNull('term')->orWhere('term', $term);
                }
            })
            ->where(function ($q) use ($session) {
                if (!$session) {
                    $q->whereNull('session');
                } else {
                    $q->whereNull('session')->orWhere('session', $session);
                }
            })
            ->get(['class_id', 'term', 'session', 'amount_due']);

        $feesByClass = $feeRows
            ->groupBy('class_id')
            ->map(function ($rows) {
                $best = null;
                $bestScore = -1;
                foreach ($rows as $row) {
                    $score = 0;
                    if ($row->term !== null) {
                        $score += 2;
                    }
                    if ($row->session !== null) {
                        $score += 1;
                    }
                    if ($score > $bestScore) {
                        $best = $row;
                        $bestScore = $score;
                    }
                }
                return $best?->amount_due ?? 0;
            });

        $paidByStudent = \App\Models\Transaction::query()
            ->whereIn('student_id', $studentIds)
            ->where('type', 'Income')
            ->where('category', 'Tuition')
            ->where('is_void', false)
            ->where('term', $term)
            ->where('session', $session)
            ->selectRaw('student_id, COALESCE(SUM(amount_paid), 0) as paid')
            ->groupBy('student_id')
            ->pluck('paid', 'student_id');

        $balances = [];
        foreach ($parents as $parent) {
            $totalDue = 0.0;
            $totalPaid = 0.0;
            
            foreach ($parentStudentsMap[$parent->id] as $student) {
                $due = (float) ($feesByClass->get($student->class_id, 0) ?? 0);
                $paid = (float) ($paidByStudent[$student->id] ?? 0);
                $totalDue += $due;
                $totalPaid += $paid;
            }

            $balances[$parent->id] = max(0.0, $totalDue - $totalPaid);
        }

        return $balances;
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
            'email' => ['required', 'email', Rule::unique('users', 'email')->where('tenant_id', TenantSettings::tenantId())],
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

    public function openEditModal(int $parentId): void
    {
        $parent = User::query()->where('role', 'parent')->whereKey($parentId)->first();
        if (!$parent) {
            return;
        }

        $this->editParentId = $parent->id;
        $this->editName = $parent->name;
        $this->editEmail = $parent->email;
        $this->editPhone = $parent->custom_fields['phone'] ?? '';
        $this->editPassword = '';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editParentId = null;
        $this->editName = '';
        $this->editEmail = '';
        $this->editPhone = '';
        $this->editPassword = '';
    }

    public function updateParent(): void
    {
        if (!$this->editParentId) {
            return;
        }

        $parent = User::query()->where('role', 'parent')->whereKey($this->editParentId)->first();
        if (!$parent) {
            return;
        }

        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->editParentId)
                    ->where('tenant_id', TenantSettings::tenantId())
            ],
            'editPhone' => 'nullable|string|max:20',
            'editPassword' => 'nullable|string|min:6',
        ]);

        $parent->name = $this->editName;
        $parent->email = $this->editEmail;

        if (!empty($this->editPassword)) {
            $parent->password = Hash::make($this->editPassword);
        }

        $customFields = $parent->custom_fields ?? [];
        if ($this->editPhone) {
            $customFields['phone'] = $this->editPhone;
        } else {
            unset($customFields['phone']);
        }
        $parent->custom_fields = empty($customFields) ? null : $customFields;

        // Also normalize phone for WhatsApp if updated
        $normalizedPhone = preg_replace('/\D+/', '', $this->editPhone);
        $normalizedPhone = $normalizedPhone !== '' ? $normalizedPhone : null;
        if ($normalizedPhone && !$parent->whatsapp_phone) {
            $parent->whatsapp_phone = $normalizedPhone;
            $parent->whatsapp_verified = true;
            $parent->whatsapp_subscribed = true;
        }

        $parent->save();

        Audit::log('parents.updated', $parent, [
            'name' => $parent->name,
            'email' => $parent->email,
        ]);

        $this->dispatch('alert', message: 'Parent account updated successfully.', type: 'success');
        $this->dispatch('refresh');
        $this->closeEditModal();
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
