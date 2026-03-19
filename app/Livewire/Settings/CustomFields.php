<?php

namespace App\Livewire\Settings;

use App\Models\CustomField;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Custom Fields')]
class CustomFields extends Component
{
    public $fields = [];
    public $showForm = false;
    public $editingField = null;
    public $filterFormType = 'all';
    
    // Form properties
    public $name = '';
    public $label = '';
    public $type = 'text';
    public $formType = 'student';
    public $required = false;
    public $options = '';
    public $placeholder = '';

    protected $rules = [
        'name' => 'required|string|max:255|regex:/^[a-z_]+$/',
        'label' => 'required|string|max:255',
        'type' => 'required|in:text,number,date,select,textarea,checkbox',
        'formType' => 'required|in:student,teacher',
        'required' => 'boolean',
        'options' => 'nullable|string',
        'placeholder' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'name.regex' => 'Use lowercase letters and underscores only.',
        'formType.required' => 'Please select which form this field belongs to.',
    ];

    public function mount()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $this->loadFields();
    }

    public function loadFields()
    {
        $query = CustomField::ordered();
        
        if ($this->filterFormType !== 'all') {
            $query->where('form_type', $this->filterFormType);
        }
        
        $this->fields = $query->get()->toArray();
    }

    public function updatedFilterFormType()
    {
        $this->loadFields();
    }

    public function addField()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editField($id)
    {
        $field = CustomField::find($id);
        if ($field) {
            $this->editingField = $id;
            $this->name = $field->name;
            $this->label = $field->label;
            $this->type = $field->type;
            $this->formType = $field->form_type ?? 'student';
            $this->required = $field->required;
            $this->options = is_array($field->options) ? implode("\n", $field->options) : '';
            $this->placeholder = $field->placeholder ?? '';
            $this->showForm = true;
        }
    }

    public function saveField()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'form_type' => $this->formType,
            'required' => $this->required,
            'placeholder' => $this->placeholder,
            'options' => $this->type === 'select' && $this->options 
                ? array_filter(array_map('trim', explode("\n", $this->options)))
                : null,
        ];

        if ($this->editingField) {
            CustomField::find($this->editingField)->update($data);
            $this->dispatch('field-updated');
        } else {
            $data['order'] = CustomField::max('order') + 1;
            CustomField::create($data);
            $this->dispatch('field-created');
        }

        $this->resetForm();
        $this->loadFields();
    }

    public function deleteField($id)
    {
        CustomField::find($id)?->delete();
        $this->loadFields();
        $this->dispatch('field-deleted');
    }

    public function toggleActive($id)
    {
        $field = CustomField::find($id);
        if ($field) {
            $field->update(['is_active' => !$field->is_active]);
            $this->loadFields();
        }
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            CustomField::where('id', $id)->update(['order' => $index + 1]);
        }
        $this->loadFields();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->label = '';
        $this->type = 'text';
        $this->formType = 'student';
        $this->required = false;
        $this->options = '';
        $this->placeholder = '';
        $this->showForm = false;
        $this->editingField = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.custom-fields');
    }
}