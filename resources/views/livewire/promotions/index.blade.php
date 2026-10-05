<div class="space-y-6">
    <x-page-header title="Promote Students" subtitle="Bulk promotion between classes and sections." accent="more">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card-padded">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-900">Promotion Filters</div>
                <div class="mt-1 text-sm text-slate-600">Pick source and destination.</div>
            </div>

            <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-4xl lg:grid-cols-4">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">From class</label>
                    <select wire:model.live="fromClassId" class="mt-2 select w-full">
                        <option value="">Select</option>
                        @foreach($this->classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">From section</label>
                    <select wire:model.live="fromSectionId" @disabled(!$fromClassId) class="mt-2 select w-full">
                        <option value="">All</option>
                        @foreach($this->fromSections as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">To class</label>
                    <select wire:model.live="toClassId" class="mt-2 select w-full">
                        <option value="">Select</option>
                        @foreach($this->classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">To section</label>
                    <select wire:model.live="toSectionId" @disabled(!$toClassId) class="mt-2 select w-full">
                        <option value="">None</option>
                        @foreach($this->toSections as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Note (optional)</label>
            <input wire:model="note" class="mt-2 input w-full" placeholder="e.g. End of term promotion" />
        </div>

        @error('selected') <div class="mt-3 text-sm text-orange-700">{{ $message }}</div> @enderror
        @error('fromClassId') <div class="mt-3 text-sm text-orange-700">{{ $message }}</div> @enderror
        @error('toSectionId') <div class="mt-3 text-sm text-orange-700">{{ $message }}</div> @enderror

        <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-gray-600">
                {{ $this->students->count() }} active student(s)
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="toggleSelectAll" @disabled(!$fromClassId) class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    {{ $selectAll ? 'Clear selection' : 'Select all' }}
                </button>
                <button type="button" wire:click="promoteSelected" @disabled(!$fromClassId || !$toClassId)
                    onclick="return confirm('Promote selected students?')" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Promote Selected
                </button>
            </div>
        </div>
    </div>

    <div class="card-padded">
        <div class="text-sm font-semibold text-gray-900">Students</div>
        <div class="mt-1 text-sm text-gray-600">Select students to promote.</div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3 w-12"></th>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Admission</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($this->students as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <input type="checkbox" wire:model.live="selected" value="{{ $student->id }}"
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $student->full_name }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $student->admission_number }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $student->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Select a class to load
                                students.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-padded">
        <div class="text-sm font-semibold text-gray-900">Recent Promotions</div>
        <div class="mt-1 text-sm text-gray-600">Last 20 actions.</div>

        <div class="mt-4 space-y-2">
            @forelse($recentPromotions as $p)
                <div class="rounded-2xl border border-gray-100 bg-white p-4 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900">
                        {{ $p->student?->full_name ?? 'Student' }} ({{ $p->student?->admission_number }})
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        {{ $p->fromClass?->name }} → {{ $p->toClass?->name }} • {{ $p->promoted_at?->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600">
                    No promotions yet.
                </div>
            @endforelse
        </div>
    </div>
</div>