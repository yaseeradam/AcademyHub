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
    public array $targetClassIds = [];
    public bool $showCopyModal = false;

    public function mount(SchoolClass $class)
    {
        $this->class = $class;
        // Store as strings so Livewire checkbox wire:model comparison works correctly
        $this->selectedSubjects = $class->defaultSubjects->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function openCopyModal(): void
    {
        $this->targetClassIds = [];
        $this->showCopyModal = true;
    }

    public function closeCopyModal(): void
    {
        $this->showCopyModal = false;
        $this->targetClassIds = [];
    }

    public function copyToClasses(): void
    {
        $this->validate([
            'targetClassIds'   => ['required', 'array', 'min:1'],
            'targetClassIds.*' => ['integer', 'exists:classes,id'],
        ]);

        $tenantId = $this->class->tenant_id;
        $syncData = [];
        foreach ($this->selectedSubjects as $subjectId) {
            $syncData[(int) $subjectId] = ['tenant_id' => $tenantId];
        }

        $targetClasses = SchoolClass::whereIn('id', $this->targetClassIds)
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $this->class->id)
            ->get();

        foreach ($targetClasses as $target) {
            $target->defaultSubjects()->sync($syncData);
        }

        $count = $targetClasses->count();
        $this->showCopyModal = false;
        $this->targetClassIds = [];
        $this->dispatchSuccessModal('Curriculum Copied', "Curriculum successfully copied to {$count} class(es).");
    }

    public function save()
    {
        $this->validate([
            'selectedSubjects'   => ['array'],
            'selectedSubjects.*' => ['integer', 'exists:subjects,id'],
        ]);

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
            'allSubjects'  => Subject::query()->orderBy('name')->get(),
            'otherClasses' => SchoolClass::query()
                ->where('tenant_id', $this->class->tenant_id)
                ->where('id', '!=', $this->class->id)
                ->orderBy('level')
                ->get(),
        ]);
    }
}
