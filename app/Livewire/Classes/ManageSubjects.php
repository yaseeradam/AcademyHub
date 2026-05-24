<?php

namespace App\Livewire\Classes;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\DispatchesModals;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manage Class Subjects')]
class ManageSubjects extends Component
{
    use DispatchesModals;

    public SchoolClass $class;
    public array $selectedSubjects = [];

    public function mount(SchoolClass $class)
    {
        $this->class = $class;
        // Store as strings so Livewire checkbox wire:model comparison works correctly
        $this->selectedSubjects = $class->defaultSubjects->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function save()
    {
        $tenantId = $this->class->tenant_id;
        $syncData = [];
        foreach ($this->selectedSubjects as $subjectId) {
            $syncData[(int) $subjectId] = ['tenant_id' => $tenantId];
        }

        $this->class->defaultSubjects()->sync($syncData);
        $this->dispatchSuccessModal('Allocation Complete', 'Class curriculum subjects have been successfully updated.');
    }

    public function render()
    {
        return view('livewire.classes.manage-subjects', [
            'allSubjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }
}
