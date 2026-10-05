<div class="space-y-6">
    <x-page-header title="Events" subtitle="Schedule and share important school events." accent="more">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
        </x-slot:actions>
    </x-page-header>

    @if($isAdmin)
        <div class="card-padded">
            <div class="text-sm font-semibold text-gray-900">Create / Edit Event</div>
            <div class="mt-1 text-sm text-gray-600">Add upcoming events and notify staff.</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Title</label>
                    <input wire:model="title" class="mt-2 input w-full" placeholder="Event title" />
                    @error('title') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Location</label>
                    <input wire:model="location" class="mt-2 input w-full" placeholder="e.g. Assembly hall" />
                    @error('location') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Starts</label>
                    <input wire:model="startsAt" type="datetime-local" class="mt-2 input w-full" />
                    @error('startsAt') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Ends (optional)</label>
                    <input wire:model="endsAt" type="datetime-local" class="mt-2 input w-full" />
                    @error('endsAt') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div class="flex items-end gap-2">
                    <button type="button" wire:click="save" class="btn-primary w-full" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        {{ $editingId ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="clearForm" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Clear</button>
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Description (optional)</label>
                <textarea wire:model="description" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Details..."></textarea>
                @error('description') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="card-padded">
            <div class="text-sm font-semibold text-gray-900">Upcoming</div>
            <div class="mt-1 text-sm text-gray-600">Events from today onward.</div>

            <div class="mt-4 space-y-3">
                @forelse($upcoming as $e)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900">{{ $e->title }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $e->starts_at?->format('M j, Y g:i A') }}
                                    @if($e->ends_at) → {{ $e->ends_at->format('g:i A') }} @endif
                                    @if($e->location) • {{ $e->location }} @endif
                                </div>
                            </div>
                            @if($isAdmin)
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="edit({{ $e->id }})" class="btn-outline">Edit</button>
                                    <button type="button" wire:click="notifyStaff({{ $e->id }})" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                                        Notify
                                    </button>
                                    <button type="button" wire:click="delete({{ $e->id }})" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                                        Delete
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($e->description)
                            <div class="mt-3 whitespace-pre-wrap text-sm text-gray-700">{{ $e->description }}</div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600">
                        No upcoming events.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card-padded">
            <div class="text-sm font-semibold text-gray-900">Past</div>
            <div class="mt-1 text-sm text-gray-600">Latest past events.</div>

            <div class="mt-4 space-y-3">
                @forelse($past as $e)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4">
                        <div class="text-sm font-bold text-gray-900">{{ $e->title }}</div>
                        <div class="mt-1 text-xs text-gray-500">
                            {{ $e->starts_at?->format('M j, Y g:i A') }}
                            @if($e->location) • {{ $e->location }} @endif
                        </div>
                        @if($e->description)
                            <div class="mt-3 whitespace-pre-wrap text-sm text-gray-700">{{ $e->description }}</div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600">
                        No past events.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

