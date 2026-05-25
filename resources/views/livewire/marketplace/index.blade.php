@php
    $getIconSvg = function($icon, $slug) {
        $icons = [
            'whatsapp' => '
                <svg class="h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>',
            'whatsapp-bot' => '
                <svg class="h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>',
            'student' => '
                <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
                </svg>',
            'student-dashboard' => '
                <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
                </svg>',
            'parent-portal' => '
                <svg class="h-6 w-6 text-pink-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>',
            'exam' => '
                <svg class="h-6 w-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>',
            'cbt' => '
                <svg class="h-6 w-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>',
            'finance' => '
                <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>',
            'savings-loan' => '
                <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>',
            'messages' => '
                <svg class="h-6 w-6 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>',
            'document' => '
                <svg class="h-6 w-6 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>',
            'homework' => '
                <svg class="h-6 w-6 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>',
            'e-learning' => '
                <svg class="h-6 w-6 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>',
        ];

        $key = isset($icons[$icon]) ? $icon : (isset($icons[$slug]) ? $slug : 'default');
        return $icons[$key] ?? '
            <svg class="h-6 w-6 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>';
    };

    $getThemeGradient = function($category) {
        $themes = [
            'Communication' => 'from-emerald-500/10 to-teal-500/5 bg-emerald-500/5 text-emerald-700 border border-emerald-500/10',
            'Education'     => 'from-indigo-500/10 to-purple-500/5 bg-indigo-500/5 text-indigo-700 border border-indigo-500/10',
            'Examination'   => 'from-blue-500/10 to-cyan-500/5 bg-blue-500/5 text-blue-700 border border-blue-500/10',
            'Finance'       => 'from-emerald-500/10 to-green-500/5 bg-emerald-500/5 text-emerald-700 border border-emerald-500/10',
            'Portal'        => 'from-pink-500/10 to-rose-500/5 bg-pink-500/5 text-pink-700 border border-pink-500/10',
        ];
        return $themes[$category] ?? 'from-slate-500/10 to-gray-500/5 bg-slate-500/5 text-slate-700 border border-slate-500/10';
    };
@endphp

{{-- All modal state lives in Alpine.js — zero server round-trips for open/close --}}
<div
    class="space-y-6"
    x-data="{
        show: false,
        processing: false,
        component: null,
        activeTab: 'all',
        installedIds: @js($installed),
        open(data) { this.component = data; this.show = true; },
        close() { if (this.processing) return; this.show = false; this.component = null; },

        async handleInstall() {
            if (!navigator.onLine) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'You are offline. Please check your connection.', type: 'error' } }));
                return;
            }
            this.processing = true;
            this.show = false;
            try {
                await $wire.confirmInstall(this.component.id);
            } catch(e) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'Request failed. Please check your connection.', type: 'error' } }));
                this.processing = false;
            }
        },

        async handlePayment() {
            if (!navigator.onLine) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'You are offline. Please check your connection.', type: 'error' } }));
                return;
            }
            this.processing = true;
            this.show = false;
            try {
                await $wire.startPayment(this.component.id);
            } catch(e) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'Request failed. Please check your connection.', type: 'error' } }));
                this.processing = false;
            }
        }
    }"
    @keydown.escape.window="close()"
>
    {{-- Premium Glassmorphism Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-8 shadow-xl sm:px-12 sm:py-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.15),transparent_45%)]"></div>
        <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-bold text-indigo-300 uppercase tracking-widest">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.5a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75h-3.5a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                    MyAcademy Marketplace
                </div>
                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">Extend Your School</h1>
                <p class="max-w-xl text-sm font-medium text-slate-400">Discover and install powerful plugins, automation bots, and student-parent portals directly into your school dashboard.</p>
            </div>
            
            {{-- Quick Stats Bar --}}
            <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md self-start md:self-auto">
                <div class="text-center px-4 border-r border-white/10">
                    <div class="text-xl font-black text-white">{{ count($components) }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Apps</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-xl font-black text-indigo-400">{{ count($installed) }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-4">
        <div class="flex flex-wrap items-center gap-1.5">
            <button
                @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
            >
                All Plugins
                <span :class="activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ count($components) }}</span>
            </button>
            
            <button
                @click="activeTab = 'installed'"
                :class="activeTab === 'installed' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
            >
                Installed
                <span :class="activeTab === 'installed' ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ count($installed) }}</span>
            </button>

            <button
                @click="activeTab = 'free'"
                :class="activeTab === 'free' ? 'bg-green-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
            >
                Free Add-ons
                <span :class="activeTab === 'free' ? 'bg-white/20 text-white' : 'bg-green-50 text-green-700'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ $components->where('price', 0)->count() }}</span>
            </button>

            <button
                @click="activeTab = 'paid'"
                :class="activeTab === 'paid' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
            >
                Premium (Paid)
                <span :class="activeTab === 'paid' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ $components->where('price', '>', 0)->count() }}</span>
            </button>
        </div>

        <a href="{{ route('more-features') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors py-2 px-3 rounded-lg hover:bg-indigo-50">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            Back to Dashboard
        </a>
    </div>

    {{-- Network toast container --}}
    <template x-teleport="body">
        <div id="network-toast-container" class="fixed top-5 right-5 z-[99999] flex flex-col gap-2 pointer-events-none"></div>
    </template>

    {{-- Redesigned Premium Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($components as $component)
            @php
                $isInstalled = in_array($component->id, $installed);
                $gradientClass = $getThemeGradient($component->category);
            @endphp
            <div
                x-show="activeTab === 'all' || 
                        (activeTab === 'installed' && installedIds.includes({{ $component->id }})) || 
                        (activeTab === 'free' && {{ $component->price == 0 ? 'true' : 'false' }}) || 
                        (activeTab === 'paid' && {{ $component->price > 0 ? 'true' : 'false' }})"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="group bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-slate-300/80 hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden"
            >
                {{-- Card Banner & Icon --}}
                <div class="p-6 pb-4 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="block">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $gradientClass }} flex items-center justify-center transition-transform group-hover:scale-105 duration-300">
                                {!! $getIconSvg($component->icon, $component->slug) !!}
                            </div>
                        </a>
                        
                        {{-- Category Badge --}}
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-black text-slate-600 uppercase tracking-widest">
                            {{ $component->category }}
                        </span>
                    </div>

                    {{-- Card Info --}}
                    <div class="flex-1 flex flex-col">
                        <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="block group/title">
                            <h3 class="text-base font-extrabold text-slate-900 group-hover/title:text-indigo-600 transition-colors leading-snug">
                                {{ $component->name }}
                            </h3>
                        </a>

                        {{-- Rating and Stats Row --}}
                        <div class="flex items-center gap-1.5 mt-2 mb-3">
                            {{-- Golden Stars --}}
                            <div class="flex items-center text-amber-400">
                                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.9 1.603-.9 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.9-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-xs font-black text-slate-800 ml-1 leading-none">{{ number_format($component->rating_avg ?? 4.8, 1) }}</span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 leading-none">({{ $component->rating_count ?? 15 }} reviews)</span>
                            <span class="text-slate-300 leading-none">•</span>
                            <span class="text-[10px] font-black text-indigo-600 leading-none uppercase tracking-wide bg-indigo-50 px-1.5 py-0.5 rounded">{{ $component->installs ?? 10 }} Installs</span>
                        </div>

                        {{-- Description --}}
                        <p class="text-xs text-slate-500 font-medium line-clamp-3 leading-relaxed flex-1">
                            {{ $component->short_description ?: $component->description }}
                        </p>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4">
                    {{-- Price Area --}}
                    <div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Price</div>
                        @if($component->price > 0)
                            <div class="text-sm font-black text-slate-800">
                                {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->price, 0) }}
                            </div>
                        @else
                            <div class="text-xs font-black text-emerald-600 uppercase tracking-wide">Free</div>
                        @endif
                    </div>

                    {{-- Actions Button --}}
                    <div>
                        @if($isInstalled)
                            <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="inline-flex items-center gap-1 py-1.5 px-3.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition-colors shadow-sm">
                                Manage
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @else
                            <button
                                @click="open({
                                    id: {{ $component->id }},
                                    name: @js($component->name),
                                    icon: @js($component->icon ?: '📦'),
                                    price: {{ $component->price }},
                                    priceFormatted: @js(config('myacademy.currency_symbol', '₦') . number_format($component->price, 2))
                                })"
                                :disabled="processing"
                                class="inline-flex items-center py-1.5 px-4 rounded-xl text-xs font-extrabold text-white bg-slate-900 hover:bg-indigo-600 disabled:opacity-60 disabled:cursor-not-allowed transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Get App
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200 border-dashed">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-4 text-sm font-extrabold text-slate-900">No plugins available</h3>
                <p class="mt-1 text-xs text-slate-500 font-medium">Check back later for new marketplace add-ons.</p>
            </div>
        @endforelse
    </div>

    {{-- Global processing overlay --}}
    <template x-teleport="body">
        <div
            x-show="processing"
            class="fixed inset-0 w-screen h-screen bg-black/40 backdrop-blur-sm flex items-center justify-center z-[9998]"
            style="display:none"
        >
            <div class="bg-white rounded-3xl px-8 py-6 shadow-2xl flex items-center gap-4 border border-slate-100">
                <svg class="animate-spin h-6 w-6 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div>
                    <p class="font-extrabold text-slate-900 leading-snug">Installing Plugin</p>
                    <p class="text-xs font-semibold text-slate-500">Please wait, configuring your school context...</p>
                </div>
            </div>
        </div>
    </template>

    {{-- Confirmation modal --}}
    <template x-teleport="body">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 w-screen h-screen bg-slate-950/60 backdrop-blur-md flex items-center justify-center z-[9999]"
            @click.self="close()"
            style="display:none"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="bg-white rounded-3xl p-6 max-w-md w-full mx-4 shadow-2xl border border-slate-100"
                @click.stop
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold" x-text="component?.icon"></div>
                    <div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Confirm Acquisition</div>
                        <h3 class="text-base font-extrabold text-slate-900 leading-tight" x-text="component?.name"></h3>
                    </div>
                </div>

                <p class="text-xs text-slate-500 font-medium leading-relaxed mb-6">
                    <template x-if="component?.price > 0">
                        <span>
                            This is a premium plugin costing
                            <strong class="text-slate-900 font-extrabold" x-text="component?.priceFormatted"></strong>.
                            Proceeding will open a secure Paystack checkout gateway to finalize payment.
                        </span>
                    </template>
                    <template x-if="!component?.price">
                        <span>This is a free plugin. Installing it will instantly register and mount its components in your school portal's dashboard.</span>
                    </template>
                </p>

                <div class="flex justify-end gap-2.5">
                    <button
                        @click="close()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-600 transition-colors focus:outline-none"
                    >
                        Cancel
                    </button>

                    {{-- Free install button --}}
                    <button
                        x-show="!component?.price"
                        @click="handleInstall()"
                        :disabled="processing"
                        class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 transition-colors focus:outline-none shadow-sm"
                    >
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" x-show="processing">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="processing ? 'Installing…' : 'Install Now'"></span>
                    </button>

                    {{-- Paid payment button --}}
                    <button
                        x-show="component?.price > 0"
                        @click="handlePayment()"
                        :disabled="processing"
                        class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 transition-colors focus:outline-none shadow-sm"
                    >
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" x-show="processing">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="processing ? 'Connecting…' : 'Proceed to Checkout'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
        <script src="https://js.paystack.co/v1/inline.js"></script>
        <script>
            // ── Network toast helper ──────────────────────────────────────────
            function showNetworkToast(msg, type = 'error') {
                const container = document.getElementById('network-toast-container');
                if (!container) return;

                const colorMap = {
                    error:   'bg-red-600 text-white',
                    warning: 'bg-amber-500 text-white',
                    success: 'bg-green-600 text-white',
                };
                const colors = colorMap[type] ?? colorMap.error;

                const toast = document.createElement('div');
                toast.className = `pointer-events-auto flex items-center gap-3 rounded-xl px-4 py-3 shadow-xl text-xs font-semibold ${colors} transition-all duration-300 opacity-0 translate-y-2`;
                toast.innerHTML = `
                    <svg class="h-4.5 w-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                    </svg>
                    <span>${msg}</span>`;

                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(0)';
                });

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-8px)';
                    setTimeout(() => toast.remove(), 300);
                }, 4500);
            }

            // Listen for custom network toast events (from Alpine methods)
            window.addEventListener('network-toast', e => {
                showNetworkToast(e.detail.msg, e.detail.type ?? 'error');
            });

            // ── Offline / back-online detection ──────────────────────────────
            window.addEventListener('offline', () => {
                showNetworkToast('You are offline. Please check your internet connection.', 'error');
            });

            window.addEventListener('online', () => {
                showNetworkToast('Back online!', 'success');
            });

            // ── Slow connection detection (warn if request takes > 5s) ───────
            let slowTimer = null;
            document.addEventListener('livewire:request', () => {
                slowTimer = setTimeout(() => {
                    showNetworkToast('Slow connection detected. This may take a moment…', 'warning');
                }, 5000);
            });
            document.addEventListener('livewire:response', () => {
                clearTimeout(slowTimer);
            });

            // ── Paystack popup ────────────────────────────────────────────────
            window.addEventListener('open-paystack', event => {
                const data = event.detail[0] ?? event.detail;

                // If offline, bail immediately
                if (!navigator.onLine) {
                    showNetworkToast('You are offline. Cannot open payment window.', 'error');
                    return;
                }

                PaystackPop.setup({
                    key: data.key,
                    email: data.email,
                    amount: data.amount,
                    ref: data.ref,
                    metadata: {
                        custom_fields: [{
                            display_name: 'Component ID',
                            variable_name: 'component_id',
                            value: data.component_id
                        }]
                    },
                    callback: response => {
                        @this.call('verifyPayment', response.reference);
                    },
                    onClose: () => {
                        // Reset processing state
                        const el = document.querySelector('[x-data]');
                        if (el?._x_dataStack?.[0]) {
                            el._x_dataStack[0].processing = false;
                        }
                        showNetworkToast('Payment cancelled. No charge was made.', 'warning');
                    }
                }).openIframe();
            });
        </script>
    @endpush
</div>