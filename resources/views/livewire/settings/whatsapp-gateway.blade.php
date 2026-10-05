<div class="space-y-6">

    {{-- Polling: use keyed child elements so Livewire rebinds timers correctly on state change --}}
    @if(!$isConnected)
        <div wire:poll.10s.visible="checkStatus" wire:key="poll-gateway-pairing"></div>
    @else
        <div wire:poll.30s.visible="checkStatus" wire:key="poll-gateway-connected"></div>
    @endif

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="{{ route('settings.index') }}" class="hover:text-slate-800 transition">Settings</a>
        <span>/</span>
        <span class="text-slate-800">WhatsApp Bot Gateway</span>
    </div>

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-64 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative px-6 py-6 sm:px-8 sm:py-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2 sm:mb-3">
                    <span class="h-2.5 w-2.5 rounded-full {{ $isConnected ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                    <span class="text-xs sm:text-sm font-semibold uppercase tracking-widest" style="color: #93c5fd;">
                        {{ $provider === 'evolution' ? 'Free Multi-Device Gateway (₦0 / $0 Cost)' : 'Meta Cloud API Gateway' }}
                    </span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight flex items-center gap-3">
                    <span>WhatsApp Gateway</span>
                    @if($isConnected)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-300 ring-1 ring-emerald-500/40">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Connected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-3 py-1 text-xs font-bold text-amber-300 ring-1 ring-amber-500/40">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span> Pairing Needed
                        </span>
                    @endif
                </h2>
                <p class="mt-2 text-xs sm:text-base font-medium max-w-2xl" style="color: #93c5fd;">
                    Send automated report cards, instant student attendance alerts, fee receipts, and AI chatbot answers directly to parents via WhatsApp with zero per-message charges.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button wire:click="checkStatus" wire:loading.attr="disabled" wire:target="checkStatus"
                        class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 text-white px-4 py-2.5 text-xs font-bold transition backdrop-blur-sm">
                    <svg wire:loading.class="animate-spin" wire:target="checkStatus" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Refresh Status
                </button>
            </div>
        </div>
    </div>

    {{-- Notifications / Feedback --}}
    @if($statusMessage)
        <div class="flex items-center gap-3 rounded-2xl bg-blue-50 px-5 py-4 ring-1 ring-blue-100 shadow-xs">
            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            <span class="text-sm font-semibold text-blue-800">{{ $statusMessage }}</span>
        </div>
    @endif

    @if($errorMessage)
        <div class="flex items-center gap-3 rounded-2xl bg-red-50 px-5 py-4 ring-1 ring-red-100 shadow-xs">
            <svg class="h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <div class="text-sm font-medium text-red-800">
                <span class="font-bold">Gateway Connection Error:</span> {{ $errorMessage }}.
                <span class="block text-xs mt-1 text-red-600">Ensure the WhatsApp container is running (<code class="bg-red-100 px-1 py-0.5 rounded">docker compose up -d whatsapp</code>).</span>
            </div>
        </div>
    @endif

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2-Cols: Connection State & QR Pairing --}}
        <div class="lg:col-span-2 space-y-6">

            @if($isConnected)
                {{-- CONNECTED STATE CARD --}}
                <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600 shrink-0">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800">Gateway Status: Online</h3>
                                <p class="text-xs text-slate-500">Your school WhatsApp account is active and connected</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Active
                        </span>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Connected Number</span>
                                <div class="text-base font-bold text-slate-800 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                    <span>{{ $connectedPhone ? preg_replace('/[:@].*$/', '', $connectedPhone) : 'Active Instance' }}</span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Architecture Rail</span>
                                <div class="text-base font-bold text-slate-800 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.75 5.1a2.25 2.25 0 011.8-1.1h8.9a2.25 2.25 0 011.8 1.1l2.6 3.45a4.5 4.5 0 01.9 2.7"/></svg>
                                    <span>Multi-Device (Baileys / Evolution)</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
                            <svg class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="text-xs text-emerald-800 space-y-1">
                                <p class="font-bold">Multi-Device Persistent Session Active</p>
                                <p>Your phone does <strong>not</strong> need to be constantly powered on or connected to the internet. WhatsApp multi-device keeps this server connected independently. You only need to open WhatsApp on your phone once every 14 days to keep the link active.</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400">Want to use a different phone number?</span>
                            <button wire:click="disconnect"
                                    wire:confirm="Are you sure you want to disconnect this WhatsApp session? You will need to scan a new QR code to reconnect."
                                    wire:loading.attr="disabled"
                                    wire:target="disconnect"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Disconnect Session
                            </button>
                        </div>
                    </div>
                </div>

            @else
                {{-- DISCONNECTED / PAIRING CARD --}}
                <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-600 shrink-0">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800">Scan QR Code to Link WhatsApp</h3>
                                <p class="text-xs text-slate-500">Pair your official school phone with the system</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                            Auto-refreshing
                        </span>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-col md:flex-row items-center gap-8">

                            {{-- QR Code Display --}}
                            <div class="flex flex-col items-center shrink-0">
                                <div class="relative p-3 rounded-2xl bg-white border-2 border-dashed border-slate-200 shadow-sm flex items-center justify-center" style="width: 240px; height: 240px;">
                                    @if($qrCode)
                                        <img src="{{ Str::startsWith($qrCode, 'data:image') ? $qrCode : 'data:image/png;base64,' . $qrCode }}"
                                             alt="WhatsApp QR Code"
                                             class="w-full h-full object-contain rounded-xl" />
                                    @else
                                        <div class="text-center p-4">
                                            <svg class="h-10 w-10 text-slate-300 mx-auto animate-pulse" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z"/>
                                            </svg>
                                            <p class="mt-2 text-xs font-semibold text-slate-500">Generating pairing code...</p>
                                        </div>
                                    @endif
                                </div>
                                <button wire:click="loadQr" wire:loading.attr="disabled" wire:target="loadQr"
                                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                    <svg wire:loading.class="animate-spin" wire:target="loadQr" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    Reload QR Code
                                </button>
                            </div>

                            {{-- Step-by-Step Instructions --}}
                            <div class="space-y-4 flex-1">
                                <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">How to connect:</h4>
                                <ol class="space-y-3 text-xs sm:text-sm text-slate-600">
                                    <li class="flex items-start gap-3">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">1</span>
                                        <span>Open <strong>WhatsApp</strong> on your school or administrative mobile phone.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">2</span>
                                        <span>Tap <strong>Menu</strong> (Android 3 dots) or <strong>Settings</strong> (iPhone).</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">3</span>
                                        <span>Select <strong>Linked Devices</strong>, then tap <strong>Link a Device</strong>.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">4</span>
                                        <span>Point your phone camera at this QR code. The system will connect automatically within seconds!</span>
                                    </li>
                                </ol>

                                <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-100 text-xs text-amber-800">
                                    <span class="font-bold">Note:</span> Once linked, the connection is saved permanently in your server's Docker volume. Server restarts and updates will not log you out.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif

            {{-- LIVE DISPATCH TEST PANEL --}}
            <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-blue-600 shrink-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Send Test Message</h3>
                            <p class="text-xs text-slate-500">Test live message delivery to verify that your gateway is communicating properly</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    @if($testFeedback)
                        <div class="p-4 rounded-xl {{ $testSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }} text-xs sm:text-sm font-semibold flex items-center gap-2">
                            @if($testSuccess)
                                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                            <span>{{ $testFeedback }}</span>
                        </div>
                    @endif

                    <form wire:submit="sendTest">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Recipient Phone</label>
                                <input type="text"
                                       wire:model="testPhone"
                                       placeholder="e.g. 2348012345678"
                                       class="w-full rounded-xl border {{ $errors->has('testPhone') ? 'border-red-400 ring-2 ring-red-100' : 'border-slate-300' }} bg-slate-50 py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100" />
                                @error('testPhone')
                                    <span class="text-[11px] text-red-500 mt-1 block font-semibold">{{ $message }}</span>
                                @else
                                    <span class="text-[11px] text-slate-400 mt-1 block">Include country code without the + symbol</span>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Message Content</label>
                                <input type="text"
                                       wire:model="testMessage"
                                       placeholder="Type a test message..."
                                       class="w-full rounded-xl border {{ $errors->has('testMessage') ? 'border-red-400 ring-2 ring-red-100' : 'border-slate-300' }} bg-slate-50 py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100" />
                                @error('testMessage')
                                    <span class="text-[11px] text-red-500 mt-1 block font-semibold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="sendTest"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 px-6 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-95 disabled:opacity-50">
                                <svg wire:loading.class="animate-spin" wire:target="sendTest" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                <span wire:loading.remove wire:target="sendTest">Send Test Message</span>
                                <span wire:loading wire:target="sendTest">Sending via Gateway...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right Col: Features & Architectural Summary --}}
        <div class="space-y-6">

            {{-- 100% Free Guarantee Card --}}
            <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-slate-100 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">100% Free Forever</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Meta recently changed their Cloud API pricing rules, charging per message from Message #1.
                </p>
                <p class="text-xs text-slate-600 leading-relaxed mt-2">
                    AcademyHub solves this by embedding an open-source Multi-Device Gateway on your server. It pairs directly with your school phone number via WhatsApp Web protocol — eliminating all Meta fees completely.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2.5 text-xs font-semibold text-slate-700">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>₦0 / $0 per-message charges</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>No Meta credit card required</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Send unlimited PDFs, receipts & alerts</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Two-way AI parent chatbot replies</span>
                    </div>
                </div>
            </div>

            {{-- Supported Automated Triggers Card --}}
            <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-slate-100 p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3">Automated Capabilities</h3>
                <ul class="space-y-3 text-xs text-slate-600">
                    <li class="flex items-start gap-2.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                        <div><strong>Report Card Dispatch:</strong> Send individual term report cards to parent WhatsApp in one click.</div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                        <div><strong>Attendance Notifications:</strong> Instant alert to parents when student arrives or is marked absent.</div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                        <div><strong>Fee Receipts:</strong> Automated PDF receipt dispatch when bursar confirms school fee payment.</div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                        <div><strong>Interactive Parent Bot:</strong> Parents text "Balance" or "Grades" to check student information instantly.</div>
                    </li>
                </ul>
            </div>

        </div>

    </div>

</div>
