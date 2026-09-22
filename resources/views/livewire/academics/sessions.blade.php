<div class="space-y-6">
    <x-page-header title="Academic Sessions & Terms" subtitle="Manage academic years, terms, and set the active term."
        accent="more">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Active Term Banner --}}
    @if($activeTerm)
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-teal-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-emerald-900">Current Active Term</div>
                    <div class="text-xs text-emerald-700">
                        {{ $activeTerm->name }} &mdash; {{ $activeTerm->academicSession->name ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-amber-900">No Active Term</div>
                    <div class="text-xs text-amber-700">Create a session, add terms, and set one as active.</div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Session Form --}}
        <div class="card-padded">
            <div class="text-sm font-semibold text-gray-900">Create / Edit Session</div>
            <div class="mt-1 text-sm text-gray-600">Example: 2026/2027</div>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                    <input wire:model="name" class="mt-2 input w-full" placeholder="2026/2027" />
                    @error('name') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Start date
                            (optional)</label>
                        <input wire:model="startsOn" type="date" class="mt-2 input w-full" />
                        @error('startsOn') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">End date
                            (optional)</label>
                        <input wire:model="endsOn" type="date" class="mt-2 input w-full" />
                        @error('endsOn') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                    </div>
                </div>

                <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                    <input wire:model="isActive" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                    Set as active session
                </label>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="save" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        {{ $editingId ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="clearForm"
                        class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold bg-white text-indigo-600 border-2 border-indigo-200 hover:bg-indigo-50" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Clear</button>
                </div>
            </div>
        </div>

        {{-- Term Form (shown when termSessionId is set) --}}
        @if($termSessionId)
            @php $termSession = $sessions->firstWhere('id', $termSessionId); @endphp
            <div class="card-padded border-2 border-brand-200">
                <div class="text-sm font-semibold text-gray-900">{{ $editingTermId ? 'Edit' : 'Add' }} Term</div>
                <div class="mt-1 text-sm text-gray-600">
                    For session: <span class="font-bold">{{ $termSession->name ?? 'N/A' }}</span>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term Name</label>
                            <input wire:model="termName" class="mt-2 input w-full" placeholder="First Term" />
                            @error('termName') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term Number</label>
                            <select wire:model="termNumber" class="mt-2 input w-full">
                                <option value="1">1 â€” First Term</option>
                                <option value="2">2 â€” Second Term</option>
                                <option value="3">3 â€” Third Term</option>
                            </select>
                            @error('termNumber') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Start date
                                (optional)</label>
                            <input wire:model="termStartsOn" type="date" class="mt-2 input w-full" />
                            @error('termStartsOn') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">End date
                                (optional)</label>
                            <input wire:model="termEndsOn" type="date" class="mt-2 input w-full" />
                            @error('termEndsOn') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="saveTerm" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            {{ $editingTermId ? 'Update Term' : 'Add Term' }}
                        </button>
                        <button type="button" wire:click="clearTermForm"
                            class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold bg-white text-indigo-600 border-2 border-indigo-200 hover:bg-indigo-50" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Clear</button>
                        <button type="button" wire:click="hideTermForm"
                            class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold bg-white text-gray-500 border-2 border-gray-200 hover:bg-gray-50" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Sessions List --}}
    <div class="card-padded">
        <div class="text-sm font-semibold text-gray-900">All Sessions & Terms</div>
        <div class="mt-1 text-sm text-gray-600">Click "Add Term" to create terms for a session. Set one term as active
            across the system.</div>

        <div class="mt-4 space-y-4">
            @forelse($sessions as $s)
                <div
                    class="rounded-2xl border {{ $s->is_active ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-100 bg-white' }} p-4">
                    {{-- Session Header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-bold text-gray-900">{{ $s->name }}</div>
                                @if($s->is_active)
                                    <span
                                        class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-700 ring-1 ring-emerald-100">Active
                                        Session</span>
                                @endif
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                @if($s->starts_on) {{ $s->starts_on->toDateString() }} @endif
                                @if($s->starts_on && $s->ends_on) â†’ @endif
                                @if($s->ends_on) {{ $s->ends_on->toDateString() }} @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!$s->is_active)
                                <button type="button" wire:click="setActive({{ $s->id }})"
                                    class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                                    Set active
                                </button>
                            @endif
                            <button type="button" wire:click="showTermForm({{ $s->id }})"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-700">
                                + Add Term
                            </button>
                            <button type="button" wire:click="edit({{ $s->id }})"
                                class="inline-flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold bg-white text-indigo-600 border-2 border-indigo-200 hover:bg-indigo-50">Edit</button>
                            <button type="button" wire:click="delete({{ $s->id }})"
                                wire:confirm="Delete this session and all its terms?"
                                class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                                Delete
                            </button>
                        </div>
                    </div>

                    {{-- Terms List --}}
                    @if($s->terms->isNotEmpty())
                        <div class="mt-3 space-y-2 border-t border-gray-100 pt-3">
                            <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Terms</div>
                            @foreach($s->terms as $term)
                                <div
                                    class="flex items-center justify-between gap-3 rounded-xl {{ $term->is_active ? 'bg-emerald-100/60 ring-1 ring-emerald-200' : 'bg-gray-50' }} px-3 py-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-full {{ $term->is_active ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600' }} text-xs font-bold">
                                            {{ $term->term_number }}
                                        </span>
                                        <div>
                                            <div class="text-xs font-semibold text-gray-800">{{ $term->name }}</div>
                                            @if($term->starts_on || $term->ends_on)
                                                <div class="text-[10px] text-gray-500">
                                                    @if($term->starts_on) {{ $term->starts_on->toDateString() }} @endif
                                                    @if($term->starts_on && $term->ends_on) â†’ @endif
                                                    @if($term->ends_on) {{ $term->ends_on->toDateString() }} @endif
                                                </div>
                                            @endif
                                        </div>
                                        @if($term->is_active)
                                            <span
                                                class="rounded-full bg-emerald-600 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white">Active</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @if(!$term->is_active)
                                            <button type="button" wire:click="setActiveTerm({{ $term->id }})"
                                                class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-2.5 py-1.5 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                Set Active
                                            </button>
                                        @endif
                                        <button type="button" wire:click="editTerm({{ $term->id }})"
                                            class="inline-flex items-center justify-center rounded-lg px-2.5 py-1.5 text-[10px] font-semibold bg-white text-indigo-600 border border-indigo-200 hover:bg-indigo-50">Edit</button>
                                        <button type="button" wire:click="deleteTerm({{ $term->id }})"
                                            wire:confirm="Delete this term?"
                                            class="inline-flex items-center justify-center rounded-lg px-2.5 py-1.5 text-[10px] font-semibold bg-white text-rose-600 border border-rose-200 hover:bg-rose-50">Delete</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-3 border-t border-gray-100 pt-3">
                            <div class="text-xs text-gray-400 italic">No terms created yet. Click "+ Add Term" to create terms
                                for this session.</div>
                        </div>
                    @endif
                </div>
            @empty
                <div
                    class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600">
                    No sessions yet.
                </div>
            @endforelse
        </div>
    </div>
</div>