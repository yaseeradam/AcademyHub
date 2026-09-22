	<div class="space-y-6">
	    <x-page-header title="Certificates" subtitle="Generate and download student certificates." accent="more">
	        <x-slot:actions>
	            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
	            @if(auth()->user()?->role === 'admin')
	                <a href="{{ route('settings.certificates') }}" class="btn-outline">Design Settings</a>
	            @endif
	        </x-slot:actions>
	    </x-page-header>

    <div class="card-padded">
        <div class="text-sm font-semibold text-gray-900">Create Certificate</div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Quick Select Class</label>
                <div class="mt-2 flex gap-2">
                    <select wire:model="classId" class="select flex-1">
                        <option value="">Select class...</option>
                        @foreach($this->classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="selectClass" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Add All</button>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Search Individual Student</label>
                <input wire:model.live.debounce.250ms="search" class="mt-2 input w-full" placeholder="Name or admission number..." />
            </div>
        </div>

        @if($search)
            <div class="mt-3 space-y-1">
                @foreach($this->students as $s)
                    <label class="flex items-center gap-2 rounded-lg border p-3 cursor-pointer hover:bg-gray-50 {{ in_array($s->id, $selectedStudents) ? 'bg-sky-50 border-sky-300' : 'border-gray-200' }}">
                        <input type="checkbox" wire:click="toggleStudent({{ $s->id }})" {{ in_array($s->id, $selectedStudents) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm font-medium">{{ $s->full_name }}</span>
                        <span class="text-xs text-gray-500">({{ $s->admission_number }})</span>
                    </label>
                @endforeach
            </div>
        @endif

        @if(count($selectedStudents) > 0)
            <div class="mt-4 rounded-lg bg-sky-50 border border-sky-200 p-3">
                <div class="text-sm font-semibold text-sky-900">{{ count($selectedStudents) }} student(s) selected</div>
                <button type="button" wire:click="$set('selectedStudents', [])" class="mt-2 text-xs text-sky-700 hover:text-sky-900" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Clear all</button>
            </div>
        @endif
        @error('selectedStudents') <div class="mt-2 text-xs text-orange-700">{{ $message }}</div> @enderror

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Type</label>
                <input wire:model="type" class="mt-2 input w-full" placeholder="e.g. Character" />
                @error('type') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
            </div>
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Title</label>
                <input wire:model="title" class="mt-2 input w-full" />
                @error('title') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Issued on</label>
                <input wire:model="issuedOn" type="date" class="mt-2 input w-full" />
                @error('issuedOn') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Body</label>
            <textarea wire:model="body" rows="6" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
            @error('body') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
        </div>

        <div class="mt-4 flex items-center justify-end">
            <button type="button" wire:click="create" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Create Certificate</button>
        </div>
    </div>

    <div class="card-padded">
        <div class="text-sm font-semibold text-gray-900">Recent Certificates</div>
        <div class="mt-1 text-sm text-gray-600">Download PDFs or delete.</div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3 text-left">Serial</th>
                    <th class="px-5 py-3 text-left">Student</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Issued</th>
                    <th class="px-5 py-3 text-left">By</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($certificates as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-xs font-mono text-gray-700">{{ $c->serial_number }}</td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $c->student?->full_name }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $c->student?->admission_number }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700">{{ $c->type }}</td>
                        <td class="px-5 py-4 text-sm text-gray-700">{{ $c->issued_on?->toDateString() }}</td>
                        <td class="px-5 py-4 text-sm text-gray-700">{{ $c->issuer?->name }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('certificates.download', $c) }}" class="btn-outline">Download</a>
                                <button type="button" wire:click="delete({{ $c->id }})" onclick="return confirm('Delete certificate?')" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No certificates yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
