@php
    $pwaSchoolName = config('academyhub.school_name');
    if (!$pwaSchoolName && app()->bound('currentTenant') && app('currentTenant')) {
        $pwaSchoolName = app('currentTenant')->name;
    }
    $pwaSchoolName = $pwaSchoolName ?: config('app.name', 'AcademyHub');
    $pwaLogo = config('academyhub.logo_url') ?: asset('icons/icon-192.png');
    $pwaColor = config('academyhub.accent_color', '#7c3aed');
@endphp

{{-- ══════════════════════════════════════════════════════════════
     CENTERED ON-DEMAND INSTALL MODAL DIALOG (Opens only on click)
     ══════════════════════════════════════════════════════════════ --}}
<div id="pwa-install-modal" 
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0 pointer-events-none"
     role="dialog" aria-modal="true" aria-labelledby="pwa-modal-title">
    
    <div id="pwa-modal-card" class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden transform scale-95 transition-all duration-300 relative">
        
        <!-- Modal Top Banner -->
        <div class="relative bg-gradient-to-br from-violet-600 via-indigo-600 to-slate-900 p-6 text-white text-center overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
            
            <button type="button" onclick="closePwaModal()" aria-label="Close"
                    class="absolute top-4 right-4 text-white/70 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="mx-auto w-16 h-16 rounded-2xl bg-white p-2 shadow-lg ring-4 ring-white/20 mb-3 flex items-center justify-center">
                <img src="{{ $pwaLogo }}" alt="{{ $pwaSchoolName }}" class="max-h-full max-w-full object-contain">
            </div>

            <h3 id="pwa-modal-title" class="text-lg font-black tracking-tight leading-snug">{{ $pwaSchoolName }}</h3>
            <p class="text-xs text-white/80 mt-1">School Mobile & Desktop App</p>
        </div>

        <!-- Modal Content -->
        <div class="p-5 sm:p-6 space-y-4">
            
            <!-- Direct 1-Tap Browser Install (Shown if browser supports native prompt) -->
            <div id="pwa-native-prompt-box" class="hidden">
                <button type="button" id="pwa-direct-install-btn" 
                        class="w-full py-3.5 px-4 rounded-2xl text-white font-extrabold text-sm shadow-lg shadow-violet-500/25 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700">
                    <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Tap Here to Install App</span>
                </button>
                <div class="flex items-center my-3">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Or follow manual steps</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>
            </div>

            <!-- Platform Tabs -->
            <div class="flex items-center p-1 bg-slate-100 rounded-xl text-xs font-bold text-slate-500">
                <button type="button" onclick="switchPwaTab('android')" id="tab-btn-android" class="flex-1 py-2 rounded-lg transition-all text-center">
                    Android
                </button>
                <button type="button" onclick="switchPwaTab('ios')" id="tab-btn-ios" class="flex-1 py-2 rounded-lg transition-all text-center">
                    iPhone / iPad
                </button>
                <button type="button" onclick="switchPwaTab('desktop')" id="tab-btn-desktop" class="flex-1 py-2 rounded-lg transition-all text-center">
                    PC / Mac
                </button>
            </div>

            <!-- Android Instructions -->
            <div id="tab-content-android" class="space-y-2.5">
                <div class="flex items-start gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <span class="font-bold text-slate-900">Chrome Menu:</span> Tap the <strong class="text-emerald-700">3 vertical dots (⋮)</strong> at the top-right corner of Chrome.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                    <div>
                        <span class="font-bold text-slate-900">Select Install:</span> Tap <strong class="text-emerald-700">"Install app"</strong> or <strong class="text-emerald-700">"Add to Home screen"</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                    <div>
                        <span class="font-bold text-slate-900">Confirm:</span> Tap <strong class="text-emerald-700">Install</strong>. AcademyHub icon will appear on your phone!
                    </div>
                </div>
            </div>

            <!-- iOS Instructions -->
            <div id="tab-content-ios" class="hidden space-y-2.5">
                <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <span class="font-bold text-slate-900">Safari Share:</span> Tap the <strong class="text-blue-700">Share icon (⎋ with arrow)</strong> at the bottom of Safari.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                    <div>
                        <span class="font-bold text-slate-900">Add to Home:</span> Scroll down and tap <strong class="text-blue-700">"Add to Home Screen" (⊞)</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                    <div>
                        <span class="font-bold text-slate-900">Done:</span> Tap <strong class="text-blue-700">Add</strong> in the top right. The app will be on your home screen!
                    </div>
                </div>
            </div>

            <!-- Desktop PC / Mac Instructions -->
            <div id="tab-content-desktop" class="hidden space-y-2.5">
                <div class="flex items-start gap-3 p-3 bg-violet-50/60 rounded-2xl border border-violet-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-violet-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <span class="font-bold text-slate-900">Address Bar:</span> Look at the right of your address bar for the <strong class="text-violet-700">Install icon (⊕ or 💻)</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-violet-50/60 rounded-2xl border border-violet-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-violet-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                    <div>
                        <span class="font-bold text-slate-900">Or Menu:</span> Click the <strong class="text-violet-700">3 dots (⋮)</strong> $ightarrow$ <strong class="text-violet-700">"Install {{ $pwaSchoolName }}"</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-violet-50/60 rounded-2xl border border-violet-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-violet-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                    <div>
                        <span class="font-bold text-slate-900">Enjoy:</span> AcademyHub opens in its own clean window without browser borders!
                    </div>
                </div>
            </div>

            <!-- Close Action -->
            <div class="pt-2">
                <button type="button" onclick="closePwaModal()" 
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    window.switchPwaTab = function(platform) {
        ['android', 'ios', 'desktop'].forEach(p => {
            const tabBtn = document.getElementById('tab-btn-' + p);
            const tabContent = document.getElementById('tab-content-' + p);
            if (tabBtn && tabContent) {
                if (p === platform) {
                    tabBtn.className = 'flex-1 py-2 rounded-lg transition-all text-center bg-white text-slate-900 shadow-sm font-extrabold';
                    tabContent.classList.remove('hidden');
                } else {
                    tabBtn.className = 'flex-1 py-2 rounded-lg transition-all text-center text-slate-500 font-bold hover:text-slate-800';
                    tabContent.classList.add('hidden');
                }
            }
        });
    };

    window.openPwaModal = function() {
        const modal = document.getElementById('pwa-install-modal');
        const card = document.getElementById('pwa-modal-card');
        if (!modal || !card) return;

        // Auto detect platform
        const ua = navigator.userAgent;
        const isIos = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        const isAndroid = /Android/.test(ua);

        if (isIos) {
            window.switchPwaTab('ios');
        } else if (isAndroid) {
            window.switchPwaTab('android');
        } else {
            window.switchPwaTab('desktop');
        }

        // Check if native prompt is available
        const nativeBox = document.getElementById('pwa-native-prompt-box');
        if (nativeBox) {
            if (window.deferredPrompt) {
                nativeBox.classList.remove('hidden');
            } else {
                nativeBox.classList.add('hidden');
            }
        }

        modal.classList.remove('hidden', 'pointer-events-none');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }, 10);
    };

    window.closePwaModal = function() {
        const modal = document.getElementById('pwa-install-modal');
        const card = document.getElementById('pwa-modal-card');
        if (!modal || !card) return;

        card.classList.remove('scale-100');
        card.classList.add('scale-95');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden', 'pointer-events-none');
        }, 300);
    };

    // Close on background click
    const modalEl = document.getElementById('pwa-install-modal');
    if (modalEl) {
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl) {
                window.closePwaModal();
            }
        });
    }

    // Global manual trigger (called from sidebar or settings)
    window.triggerPwaInstall = function() {
        if (window.deferredPrompt) {
            window.deferredPrompt.prompt();
            window.deferredPrompt.userChoice.then((choice) => {
                if (choice.outcome === 'accepted') {
                    window.closePwaModal();
                }
                window.deferredPrompt = null;
                const nativeBox = document.getElementById('pwa-native-prompt-box');
                if (nativeBox) nativeBox.classList.add('hidden');
            }).catch(() => {
                window.openPwaModal();
            });
        } else {
            window.openPwaModal();
        }
    };

    function initPwa() {
        // If running inside standalone app, hide install buttons
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) {
            document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.add('hidden'));
            return;
        }

        document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.remove('hidden'));

        const directInstallBtn = document.getElementById('pwa-direct-install-btn');
        if (directInstallBtn) {
            directInstallBtn.onclick = () => {
                if (window.deferredPrompt) {
                    window.deferredPrompt.prompt();
                    window.deferredPrompt.userChoice.then((choice) => {
                        if (choice.outcome === 'accepted') {
                            window.closePwaModal();
                        }
                        window.deferredPrompt = null;
                        const nativeBox = document.getElementById('pwa-native-prompt-box');
                        if (nativeBox) nativeBox.classList.add('hidden');
                    });
                }
            };
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            const nativeBox = document.getElementById('pwa-native-prompt-box');
            if (nativeBox) nativeBox.classList.remove('hidden');
        });
    }

    initPwa();
    document.addEventListener('livewire:navigated', initPwa);
})();
</script>
