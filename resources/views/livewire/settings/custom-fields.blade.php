<div class="space-y-6">
    <x-page-header
        title="Custom Fields"
        subtitle="Manage additional fields for student, teacher & parent registration."
        accent="settings"
    >
        <x-slot:actions>
            <button wire:click="addField" class="btn-primary">
                <svg class="mr-1.5 -ml-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Field
            </button>
        </x-slot:actions>
    </x-page-header>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-1 rounded-xl border border-gray-100 bg-white p-1.5 shadow-sm w-fit">
        <button wire:click="$set('filterFormType', 'all')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $filterFormType === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            All Fields
        </button>
        <button wire:click="$set('filterFormType', 'student')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $filterFormType === 'student' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
            Student Form
        </button>
        <button wire:click="$set('filterFormType', 'teacher')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $filterFormType === 'teacher' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Teacher Form
        </button>
        <button wire:click="$set('filterFormType', 'parent')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $filterFormType === 'parent' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Parent Form
        </button>
    </div>

    <!-- Fields List -->
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Current Fields</h3>
            <p class="mt-1 text-sm text-gray-600">Drag to reorder fields</p>
        </div>

        @if(count($fields) > 0)
            <div class="divide-y divide-gray-100">
                @foreach($fields as $field)
                    <div class="flex items-center justify-between p-6" wire:key="field-{{ $field['id'] }}">
                        <div class="flex items-center gap-4">
                            <div class="cursor-move text-gray-400">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 2a2 2 0 1 1 .001 4.001A2 2 0 0 1 7 2zM7 8a2 2 0 1 1 .001 4.001A2 2 0 0 1 7 8zM7 14a2 2 0 1 1 .001 4.001A2 2 0 0 1 7 14zM13 2a2 2 0 1 1 .001 4.001A2 2 0 0 1 13 2zM13 8a2 2 0 1 1 .001 4.001A2 2 0 0 1 13 8zM13 14a2 2 0 1 1 .001 4.001A2 2 0 0 1 13 14z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium text-gray-900">{{ $field['label'] }}</h4>
                                    @if(($field['form_type'] ?? 'student') === 'student')
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Student</span>
                                    @elseif(($field['form_type'] ?? '') === 'parent')
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">Parent</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Teacher</span>
                                    @endif
                                    @if($field['required'])
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Required</span>
                                    @endif
                                    @if(!$field['is_active'])
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">Inactive</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600">{{ ucfirst($field['type']) }} field ({{ $field['name'] }})</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleActive({{ $field['id'] }})" 
                                    class="text-sm {{ $field['is_active'] ? 'text-orange-600 hover:text-orange-700' : 'text-green-600 hover:text-green-700' }}">
                                {{ $field['is_active'] ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button wire:click="editField({{ $field['id'] }})" class="text-sm text-blue-600 hover:text-blue-700">
                                Edit
                            </button>
                            <button wire:click="deleteField({{ $field['id'] }})" 
                                    onclick="return confirm('Delete this field? This will remove all data for this field from existing records.')"
                                    class="text-sm text-red-600 hover:text-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900">No custom fields</h3>
                <p class="mt-2 text-sm text-gray-500">Get started by adding your first custom field.</p>
            </div>
        @endif
    </div>

    <!-- Add/Edit Form Modal -->
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" wire:click.self="resetForm">
            <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-start justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingField ? 'Edit Field' : 'Add Field' }}
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-500">Fields will appear on the selected form during registration.</p>
                    </div>
                    <button type="button" wire:click="resetForm" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveField" class="space-y-4">
                    <!-- Form Type Selector -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Add to Form</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative flex cursor-pointer items-center rounded-xl border-2 p-2.5 transition-all
                                {{ $formType === 'student' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                <input type="radio" wire:model.live="formType" value="student" class="sr-only">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg {{ $formType === 'student' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold {{ $formType === 'student' ? 'text-blue-900' : 'text-gray-900' }}">Student</div>
                                        <div class="text-[10px] {{ $formType === 'student' ? 'text-blue-600' : 'text-gray-500' }}">Registration</div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer items-center rounded-xl border-2 p-2.5 transition-all
                                {{ $formType === 'teacher' ? 'border-purple-500 bg-purple-50 ring-1 ring-purple-500' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                <input type="radio" wire:model.live="formType" value="teacher" class="sr-only">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg {{ $formType === 'teacher' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold {{ $formType === 'teacher' ? 'text-purple-900' : 'text-gray-900' }}">Teacher</div>
                                        <div class="text-[10px] {{ $formType === 'teacher' ? 'text-purple-600' : 'text-gray-500' }}">Staff form</div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer items-center rounded-xl border-2 p-2.5 transition-all
                                {{ $formType === 'parent' ? 'border-green-500 bg-green-50 ring-1 ring-green-500' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                <input type="radio" wire:model.live="formType" value="parent" class="sr-only">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg {{ $formType === 'parent' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold {{ $formType === 'parent' ? 'text-green-900' : 'text-gray-900' }}">Parent</div>
                                        <div class="text-[10px] {{ $formType === 'parent' ? 'text-green-600' : 'text-gray-500' }}">Parent form</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('formType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- 2-Column Grid for Field Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Field Name</label>
                            <input wire:model="name" type="text" placeholder="e.g. middle_name" 
                                   class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-0.5 text-[10px] text-gray-500">Lowercase & underscores only</p>
                            @error('name') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Display Label</label>
                            <input wire:model="label" type="text" placeholder="e.g. Middle Name" 
                                   class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('label') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Field Type</label>
                            <select wire:model.live="type" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="select">Dropdown</option>
                                <option value="textarea">Textarea</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                            @error('type') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Placeholder</label>
                            <input wire:model="placeholder" type="text" placeholder="Enter placeholder text" 
                                   class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('placeholder') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if($type === 'select')
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Options</label>
                            <textarea wire:model="options" rows="2.5" placeholder="Option 1&#10;Option 2&#10;Option 3" 
                                      class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            <p class="mt-0.5 text-[10px] text-gray-500">One option per line</p>
                            @error('options') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex items-center pt-1">
                        <input wire:model="required" type="checkbox" id="required" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="required" class="ml-2 text-xs font-medium text-gray-700">Required field</label>
                    </div>

                    <div class="flex gap-3 pt-3 border-t border-gray-100">
                        <button type="submit" 
                                class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-all"
                                wire:loading.attr="disabled"
                                wire:target="saveField">
                            <!-- Loader spinner -->
                            <svg wire:loading wire:target="saveField" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveField">
                                {{ $editingField ? 'Update Field' : 'Create Field' }}
                            </span>
                            <span wire:loading wire:target="saveField">
                                {{ $editingField ? 'Updating...' : 'Creating...' }}
                            </span>
                        </button>
                        <button type="button" wire:click="resetForm" class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>