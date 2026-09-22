<div class="space-y-6">
    <x-page-header title="Data Collection Submissions" subtitle="Approve or reject weekly teacher submissions." accent="attendance" />

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('data-collection') }}" class="btn-outline">Open Data Collection</a>
        <a href="{{ route('dashboard') }}" class="btn-outline">Dashboard</a>
    </div>

    <div class="card-padded">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                <select wire:model.live="classId" class="mt-2 select">
                    <option value="">All classes</option>
                    @foreach ($this->classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                <select wire:model.live="term" class="mt-2 select">
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                <input wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026" class="mt-2 input-compact" />
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status</label>
                <select wire:model.live="status" class="mt-2 select">
                    <option value="submitted">Submitted</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-padded overflow-hidden">
        <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-black text-gray-900">Submissions</div>
            <div class="text-xs font-semibold text-gray-500">Showing up to 80</div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-xs font-black uppercase tracking-wider text-gray-600">
                        <th class="px-3 py-2">Week</th>
                        <th class="px-3 py-2">Class</th>
                        <th class="px-3 py-2">Teacher</th>
                        <th class="px-3 py-2 text-right">Boys (P/A)</th>
                        <th class="px-3 py-2 text-right">Girls (P/A)</th>
                        <th class="px-3 py-2 text-right">Total (P/A)</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->rows as $r)
                        @php
                            $present = (int) $r->boys_present + (int) $r->girls_present;
                            $absent = (int) $r->boys_absent + (int) $r->girls_absent;
                        @endphp
                        <tr class="text-sm">
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $r->week_start?->format('M j') }}â€“{{ $r->week_end?->format('M j') }}</div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-500">
                                    @if ($r->submitted_at)
                                        Submitted {{ $r->submitted_at->format('g:i A') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $r->schoolClass?->name ?? '-' }}</div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-500">{{ $r->section?->name ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-semibold text-slate-900">{{ $r->teacher?->name ?? '-' }}</div>
                                @if ($r->school_days)
                                    <div class="mt-0.5 text-xs font-semibold text-slate-500">School days: {{ $r->school_days }}</div>
                                @endif
                                @if ($r->note)
                                    <div class="mt-1 text-xs text-slate-600">{{ $r->note }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right font-semibold">
                                {{ (int) $r->boys_present }} / {{ (int) $r->boys_absent }}
                            </td>
                            <td class="px-3 py-3 text-right font-semibold">
                                {{ (int) $r->girls_present }} / {{ (int) $r->girls_absent }}
                            </td>
                            <td class="px-3 py-3 text-right font-black">
                                {{ $present }} / {{ $absent }}
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @php($st = (string) $r->status)
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider
                                    {{ $st === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($st === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $st }}
                                </span>
                                @if ($st !== 'submitted' && $r->reviewer?->name)
                                    <div class="mt-1 text-xs font-semibold text-slate-500">By {{ $r->reviewer->name }}</div>
                                @endif
                                @if ($st === 'rejected' && $r->rejection_note)
                                    <div class="mt-1 text-xs font-semibold text-rose-700">{{ $r->rejection_note }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @if ($r->status === 'submitted')
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="btn-primary" wire:click="approve({{ $r->id }})">Approve</button>
                                        <button type="button" class="btn-outline" wire:click="startReject({{ $r->id }})">Reject</button>
                                    </div>
                                @else
                                    <span class="text-xs font-semibold text-slate-500">â€”</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">No submissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-data="{ open: @entangle('rejectingId') }" x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4" @click.self="$wire.cancelReject()" role="dialog" aria-modal="true">
        <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-black/5" @click.stop>
            <div class="text-lg font-black text-slate-900">Reject submission</div>
            <div class="mt-1 text-sm font-semibold text-slate-600">Add a short note so the teacher can fix it and resubmit.</div>

            <div class="mt-4">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rejection note</label>
                <textarea wire:model.live="rejectNote" rows="4" class="mt-2 input-compact" placeholder="e.g. Numbers don't match class size"></textarea>
                @error('rejectNote')
                    <div class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-5 flex gap-2">
                <button type="button" class="btn-outline flex-1" wire:click="cancelReject" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
                <button type="button" class="btn-primary flex-1" wire:click="confirmReject" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Reject</button>
            </div>
        </div>
    </div>
</div>
