<div class="space-y-6">
    <x-page-header title="Users" subtitle="Create accounts, assign roles, and manage activation." accent="settings" />

    @php
        $permissionDefinitions = (array) config('permissions.definitions', []);
    @endphp

    <div class="card-padded">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-3">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Name or email"
                    class="mt-2 input-compact" />
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Role</label>
                <select wire:model.live="roleFilter" class="mt-2 select">
                    <option value="">All</option>
                    <option value="admin">Admin</option>
                    <option value="bursar">Bursar</option>
                    <option value="teacher">Teacher</option>
                    <option value="parent">Parent</option>
                    <option value="proprietor">Proprietor (Owner)</option>
                </select>
            </div>

            <div class="lg:col-span-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status</label>
                <select wire:model.live="statusFilter" class="mt-2 select">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-padded">
        <div class="text-sm font-semibold text-gray-900">Create User</div>

        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-orange-200 bg-orange-50/60 p-4">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit="createUser" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Name</label>
                <input wire:model.live="name" type="text" class="mt-2 input-compact" placeholder="Full name" />
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Email</label>
                <input wire:model.live="email" type="email" class="mt-2 input-compact"
                    placeholder="user@school.local" />
            </div>

            <div class="lg:col-span-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Role</label>
                <select wire:model.live="role" class="mt-2 select">
                    <option value="teacher">Teacher</option>
                    <option value="bursar">Bursar</option>
                    <option value="admin">Admin</option>
                    <option value="parent">Parent</option>
                    <option value="proprietor">Proprietor (Owner)</option>
                </select>
            </div>

            <div class="lg:col-span-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active</label>
                <select wire:model.live="isActive" class="mt-2 select">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Password (optional)</label>
                <input wire:model.live="password" type="text" class="mt-2 input-compact"
                    placeholder="Leave empty to auto-generate" />
                <div class="mt-1 text-xs text-gray-500">If blank, a strong password is generated.</div>
            </div>

            {{-- Role-Specific Custom Fields (Create) --}}
            @php $createFields = match($role) { 'parent' => $this->parentCustomFields, 'teacher' => $this->teacherCustomFields, default => collect() }; @endphp
            @if($createFields->count() > 0)
                @foreach($createFields as $field)
                    <div class="lg:col-span-3" wire:key="create-cf-{{ $field->id }}">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ $field->label }}
                            @if($field->required) <span class="text-red-500">*</span> @endif
                        </label>
                        @if($field->type === 'select')
                            <select wire:model.live="customFieldValues.{{ $field->name }}" class="mt-2 select">
                                <option value="">Select...</option>
                                @foreach($field->options ?? [] as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif($field->type === 'textarea')
                            <textarea wire:model.live="customFieldValues.{{ $field->name }}"
                                class="mt-2 input-compact" rows="2"
                                placeholder="{{ $field->placeholder }}"></textarea>
                        @elseif($field->type === 'checkbox')
                            <div class="mt-3 flex items-center">
                                <input wire:model.live="customFieldValues.{{ $field->name }}"
                                    type="checkbox" id="cf_{{ $field->name }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="cf_{{ $field->name }}" class="ml-2 text-sm text-gray-700">{{ $field->placeholder ?: 'Yes' }}</label>
                            </div>
                        @else
                            <input wire:model.live="customFieldValues.{{ $field->name }}"
                                type="{{ $field->type }}" class="mt-2 input-compact"
                                placeholder="{{ $field->placeholder }}" />
                        @endif
                        @error("customFieldValues.{$field->name}")
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            @endif

            <div class="lg:col-span-3 flex items-end justify-end">
                <button type="submit" class="btn-primary w-full justify-center sm:w-auto inline-flex items-center gap-2"
                        wire:loading.attr="disabled" wire:target="createUser">
                    <svg wire:loading wire:target="createUser" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="createUser">Create</span>
                    <span wire:loading wire:target="createUser">Creating...</span>
                </button>
            </div>
        </form>
    </div>

    <div class="card-padded">
        <div class="flex items-center justify-between gap-4">
            <div class="text-sm font-semibold text-gray-900">All Users</div>
            <div class="text-xs text-gray-500">{{ number_format((int) $this->users->total()) }} total</div>
        </div>

        <div class="mt-4">
            {{-- Users Grid --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($this->users as $user)
                    @php
                        $roleConfig = [
                            'admin'      => ['bar' => 'bg-red-500',    'badge' => 'bg-red-100 text-red-700',        'avatar' => 'from-red-400 to-rose-500'],
                            'teacher'    => ['bar' => 'bg-blue-500',   'badge' => 'bg-blue-100 text-blue-700',      'avatar' => 'from-blue-400 to-indigo-500'],
                            'bursar'     => ['bar' => 'bg-emerald-500','badge' => 'bg-emerald-100 text-emerald-700','avatar' => 'from-emerald-400 to-teal-500'],
                            'parent'     => ['bar' => 'bg-orange-500', 'badge' => 'bg-orange-100 text-orange-700',  'avatar' => 'from-orange-400 to-amber-500'],
                            'proprietor' => ['bar' => 'bg-purple-500', 'badge' => 'bg-purple-100 text-purple-700',  'avatar' => 'from-purple-400 to-violet-500'],
                        ][$user->role] ?? ['bar' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-600', 'avatar' => 'from-slate-400 to-slate-500'];
                        $initials = collect(explode(' ', $user->name))->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                    @endphp
                    <div class="flex flex-col rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden" wire:key="user-card-{{ $user->id }}">
                        <div class="h-1 w-full {{ $roleConfig['bar'] }}"></div>
                        <div class="flex flex-col items-center px-4 pt-5 pb-4 gap-3">
                            <div class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br {{ $roleConfig['avatar'] }} text-xl font-black text-white ring-4 ring-slate-100 shadow-sm">
                                {{ $initials }}
                            </div>
                            <div class="text-center w-full">
                                <div class="text-sm font-extrabold text-slate-900 truncate">{{ $user->name }}</div>
                                <div class="text-[11px] font-medium text-slate-400 truncate mt-0.5">{{ $user->email }}</div>
                            </div>
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="text-[9px] font-black px-2.5 py-0.5 rounded-full {{ $roleConfig['badge'] }} uppercase tracking-wide">{{ ucfirst($user->role) }}</span>
                                @if($user->is_active)
                                    <span class="text-[9px] font-black px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 uppercase tracking-wide">Active</span>
                                @else
                                    <span class="text-[9px] font-black px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 uppercase tracking-wide">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="px-4 pb-4 mt-auto">
                            <button type="button" wire:click="startEdit({{ $user->id }})" wire:key="user-row-{{ $user->id }}"
                                class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 py-2.5 text-xs font-extrabold text-slate-700 transition-all">
                                Edit User
                            </button>
                        </div>
                    </div>

                    @if ($editingUserId === $user->id)
                    <div class="col-span-full rounded-2xl border border-slate-200 bg-slate-50 p-5" wire:key="user-edit-{{ $user->id }}">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                                        <div class="lg:col-span-2">
                                            <label
                                                class="text-xs font-semibold uppercase tracking-wider text-gray-500">Role</label>
                                            <select wire:model.live="editRole" class="mt-2 select">
                                                <option value="teacher">Teacher</option>
                                                <option value="bursar">Bursar</option>
                                                <option value="admin">Admin</option>
                                                <option value="parent">Parent</option>
                                                <option value="proprietor">Proprietor (Owner)</option>
                                            </select>
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label
                                                class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active</label>
                                            <select wire:model.live="editIsActive" class="mt-2 select">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                            @error('editIsActive')
                                                <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">New
                                                Password (optional)</label>
                                            <input wire:model.live="newPassword" type="text" class="mt-2 input-compact"
                                                placeholder="Min 8 characters" />
                                        </div>

                                        {{-- Role-Specific Custom Fields (Edit) --}}
                                        @php $editFields = match($editRole) { 'parent' => $this->parentCustomFields, 'teacher' => $this->teacherCustomFields, default => collect() }; @endphp
                                        @if($editFields->count() > 0)
                                            @foreach($editFields as $field)
                                                <div class="lg:col-span-2" wire:key="edit-cf-{{ $field->id }}-{{ $user->id }}">
                                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                                        {{ $field->label }}
                                                        @if($field->required) <span class="text-red-500">*</span> @endif
                                                    </label>
                                                    @if($field->type === 'select')
                                                        <select wire:model.live="editCustomFieldValues.{{ $field->name }}" class="mt-2 select">
                                                            <option value="">Select...</option>
                                                            @foreach($field->options ?? [] as $opt)
                                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($field->type === 'textarea')
                                                        <textarea wire:model.live="editCustomFieldValues.{{ $field->name }}"
                                                            class="mt-2 input-compact" rows="2"
                                                            placeholder="{{ $field->placeholder }}"></textarea>
                                                    @elseif($field->type === 'checkbox')
                                                        <div class="mt-3 flex items-center">
                                                            <input wire:model.live="editCustomFieldValues.{{ $field->name }}"
                                                                type="checkbox" id="edit_cf_{{ $field->name }}"
                                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                            <label for="edit_cf_{{ $field->name }}" class="ml-2 text-sm text-gray-700">{{ $field->placeholder ?: 'Yes' }}</label>
                                                        </div>
                                                    @else
                                                        <input wire:model.live="editCustomFieldValues.{{ $field->name }}"
                                                            type="{{ $field->type }}" class="mt-2 input-compact"
                                                            placeholder="{{ $field->placeholder }}" />
                                                    @endif
                                                    @error("editCustomFieldValues.{$field->name}")
                                                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        @endif

                                        <div class="lg:col-span-6">
                                            <div class="mt-2 flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">Permissions</div>
                                                    <div class="mt-1 text-xs text-gray-600">Default permissions come from the
                                                        selected role. You can override per user.</div>
                                                </div>
                                            </div>

                                            <div class="mt-3 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                                                <div class="grid grid-cols-1 divide-y divide-gray-100">
                                                    @forelse ($permissionDefinitions as $key => $def)
                                                                                        @php
                                                                                            $label = (string) ($def['label'] ?? $key);
                                                                                            $roles = (array) ($def['roles'] ?? []);
                                                                                            $state = (string) ($editPermissions[$key] ?? 'default');
                                                                                            $defaultAllowed = in_array($editRole, $roles, true);
                                                                                            $effectiveAllowed = $state === 'revoke' ? false : ($state === 'grant' ? true : $defaultAllowed);
                                                                                        @endphp
                                                         <div
                                                                                            class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between" wire:key="perm-{{ $key }}-{{ $user->id }}">
                                                                                            <div class="min-w-0">
                                                                                                <div class="text-sm font-semibold text-gray-900">{{ $label }}</div>
                                                                                                <div class="mt-1 text-xs text-gray-500 font-mono">{{ $key }}</div>
                                                                                            </div>

                                                                                            <div class="flex flex-wrap items-center gap-2">
                                                                                                <x-status-badge
                                                                                                    variant="{{ $effectiveAllowed ? 'success' : 'warning' }}">
                                                                                                    {{ $effectiveAllowed ? 'Allowed' : 'Denied' }}
                                                                                                </x-status-badge>
                                                                                                <select wire:model.live="editPermissions.{{ $key }}" class="select">
                                                                                                    <option value="default">Default</option>
                                                                                                    <option value="grant">Grant</option>
                                                                                                    <option value="revoke">Revoke</option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                    @empty
                                                        <div class="p-4 text-sm text-gray-600">No permissions configured.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        <div class="lg:col-span-6 flex flex-wrap justify-end gap-2">
                                            <button type="button" wire:click="cancelEdit" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
                                            <button type="button" wire:click="saveEdit" class="btn-primary inline-flex items-center gap-2"
                                                    wire:loading.attr="disabled" wire:target="saveEdit">
                                                <svg wire:loading wire:target="saveEdit" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span wire:loading.remove wire:target="saveEdit">Save</span>
                                                <span wire:loading wire:target="saveEdit">Saving...</span>
                                            </button>
                                        </div>
                                    </div>
                    </div>
                    @endif
                @empty
                    <div class="col-span-full py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100">
                                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                            <div class="text-sm font-semibold text-slate-600">No users found.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4">
            {{ $this->users->links() }}
        </div>
    </div>
</div>