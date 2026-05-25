<div class="space-y-6">
    <x-page-header title="Announcements" subtitle="Stay updated with school-wide notices and important information." accent="more">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
        </x-slot:actions>
    </x-page-header>

    @if ($isAdmin)
        <div class="rounded-2xl border-2 border-slate-300 bg-slate-100/70 p-6 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 bg-slate-200/60 p-5 rounded-2xl border-2 border-slate-300 lg:max-w-xs w-full shadow-sm">
                    <div class="text-base font-extrabold text-slate-950 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                        Create / Edit Notice
                    </div>
                    <div class="mt-2.5 text-sm font-semibold text-slate-700 leading-relaxed">Draft announcements here, then publish them to your chosen target audience when they are ready.</div>
                </div>

                <div class="w-full lg:max-w-2xl bg-white p-6 rounded-2xl border-2 border-slate-300 shadow-inner">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <div class="lg:col-span-2">
                            <label class="text-sm font-extrabold uppercase tracking-wide text-slate-900 block mb-1">Announcement Title</label>
                            <input wire:model="title" class="mt-1 w-full rounded-xl border-2 border-slate-400 bg-slate-50 px-4 py-3 text-sm text-slate-950 font-bold shadow-sm transition-all focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 placeholder-slate-400" placeholder="e.g. End of Term Examination Notice" />
                            @error('title') <div class="mt-1.5 text-xs font-bold text-rose-700 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-extrabold uppercase tracking-wide text-slate-900 block mb-1">Target Audience</label>
                            <select wire:model="audience" class="mt-1 w-full rounded-xl border-2 border-slate-400 bg-slate-50 px-3 py-3 text-sm text-slate-950 font-bold shadow-sm transition-all focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">
                                <option value="all">Everyone (All)</option>
                                <option value="staff">Staff Only</option>
                                <option value="admin">Admins Only</option>
                                <option value="teacher">Teachers Only</option>
                                <option value="bursar">Bursars Only</option>
                            </select>
                            @error('audience') <div class="mt-1.5 text-xs font-bold text-rose-700 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="text-sm font-extrabold uppercase tracking-wide text-slate-900 block mb-1">Announcement Body / Content</label>
                        <textarea wire:model="body" rows="5" class="mt-1 w-full rounded-xl border-2 border-slate-400 bg-slate-50 px-4 py-3 text-sm text-slate-950 font-semibold shadow-sm transition-all focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 placeholder-slate-400" placeholder="Write the announcement description and details here..."></textarea>
                        @error('body') <div class="mt-1.5 text-xs font-bold text-rose-700 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <button type="button" wire:click="save" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-black text-white hover:bg-amber-600 shadow-md transition-all">
                            {{ $editingId ? 'Update Draft' : 'Save Draft' }}
                        </button>
                        <button type="button" wire:click="clearForm" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold bg-slate-100 text-slate-700 border-2 border-slate-400 hover:bg-slate-200 transition-all shadow-sm">
                            Clear Form
                        </button>
                        @if ($editingId)
                            <button type="button" wire:click="publish({{ $editingId }})" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-black text-white hover:bg-emerald-700 shadow-md transition-all">
                                Publish Notice
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-2">
        @forelse($announcements as $a)
            <div class="group relative overflow-hidden rounded-2xl border-2 border-slate-300 bg-gradient-to-br from-white to-slate-50 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-indigo-400">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-100/40 to-purple-100/40 rounded-bl-full transform translate-x-12 -translate-y-12"></div>
                
                <div class="relative p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-extrabold text-slate-950 leading-tight mb-1">{{ $a->title }}</h3>
                                    <div class="flex items-center gap-2 text-xs">
                                        @php
                                            $audienceColors = [
                                                'all' => 'bg-blue-100 text-blue-800 border border-blue-300',
                                                'staff' => 'bg-purple-100 text-purple-800 border border-purple-300',
                                                'admin' => 'bg-red-100 text-red-800 border border-red-300',
                                                'teacher' => 'bg-green-100 text-green-800 border border-green-300',
                                                'bursar' => 'bg-orange-100 text-orange-800 border border-orange-300',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold {{ $audienceColors[$a->audience] ?? 'bg-slate-100 text-slate-800 border border-slate-300' }}">
                                            {{ ucfirst($a->audience) }}
                                        </span>
                                        <span class="text-slate-400">•</span>
                                        <span class="text-slate-800 font-extrabold">
                                            @if($a->published_at)
                                                {{ $a->published_at->diffForHumans() }}
                                            @else
                                                <span class="text-amber-700">Draft</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pl-0 border-t border-slate-200/80 pt-3">
                        <div class="prose prose-sm max-w-none">
                            <p class="text-slate-900 font-semibold leading-relaxed whitespace-pre-wrap text-sm">{{ $a->body }}</p>
                        </div>
                    </div>

                    @if ($isAdmin)
                        <div class="mt-5 pt-4 border-t border-gray-200 flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="edit({{ $a->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit
                            </button>
                            @if($a->published_at)
                                <button type="button" wire:click="unpublish({{ $a->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    Unpublish
                                </button>
                            @else
                                <button type="button" wire:click="publish({{ $a->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Publish
                                </button>
                            @endif
                            <button type="button" wire:click="delete({{ $a->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="lg:col-span-2">
                <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gradient-to-br from-gray-50 to-white p-12 text-center">
                    <div class="mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No announcements yet</h3>
                    <p class="text-sm text-gray-600">Check back later for important updates and notices.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

