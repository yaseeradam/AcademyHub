<div>
    @if ($show)
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center p-4"
             x-data="{ show: @entangle('show') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$wire.close()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            {{-- Modal Card --}}
            <div class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95">

                {{-- Colored top accent bar --}}
                <div class="h-1.5 w-full
                    @if($type === 'success') bg-gradient-to-r from-emerald-400 to-green-500
                    @elseif($type === 'error') bg-gradient-to-r from-red-400 to-rose-500
                    @elseif($type === 'warning') bg-gradient-to-r from-amber-400 to-orange-500
                    @else bg-gradient-to-r from-blue-400 to-indigo-500
                    @endif">
                </div>

                <div class="p-6">
                    {{-- Icon + Close --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            {{-- Icon badge --}}
                            @if($type === 'success')
                                <div class="grid h-11 w-11 flex-shrink-0 place-items-center rounded-xl bg-emerald-50">
                                    <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @elseif($type === 'error')
                                <div class="grid h-11 w-11 flex-shrink-0 place-items-center rounded-xl bg-red-50">
                                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            @elseif($type === 'warning')
                                <div class="grid h-11 w-11 flex-shrink-0 place-items-center rounded-xl bg-amber-50">
                                    <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="grid h-11 w-11 flex-shrink-0 place-items-center rounded-xl bg-blue-50">
                                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif

                            <div>
                                <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    @if($type === 'success') Action completed
                                    @elseif($type === 'error') Something went wrong
                                    @elseif($type === 'warning') Attention required
                                    @else Information
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Close X --}}
                        <button wire:click="close"
                            class="flex-shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Divider --}}
                    <div class="my-4 border-t border-slate-100"></div>

                    {{-- Message --}}
                    <p class="text-sm leading-relaxed text-slate-600">{{ $message }}</p>

                    {{-- Actions --}}
                    <div class="mt-5 flex justify-end gap-2">
                        <button wire:click="close"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Dismiss
                        </button>
                        <button wire:click="close"
                            class="rounded-xl px-4 py-2 text-sm font-bold text-white shadow-sm transition
                            @if($type === 'success') bg-gradient-to-br from-emerald-400 to-green-500 hover:from-emerald-500 hover:to-green-600
                            @elseif($type === 'error') bg-gradient-to-br from-red-400 to-rose-500 hover:from-red-500 hover:to-rose-600
                            @elseif($type === 'warning') bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600
                            @else bg-gradient-to-br from-blue-400 to-indigo-500 hover:from-blue-500 hover:to-indigo-600
                            @endif">
                            Got it
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
