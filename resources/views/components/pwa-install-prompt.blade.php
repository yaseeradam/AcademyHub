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
     1. BOTTOM FLOATING QUICK-INSTALL TOAST / BANNER
     ══════════════════════════════════════════════════════════════ --}}
<div id="pwa-install-banner" class="hidden fixed bottom-4 right-4 left-4 sm:left-auto z-50 transition-all duration-500 transform translate-y-32 opacity-0 pointer-events-none max-w-sm sm:w-96">
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/80 p-4 sm:p-5 flex flex-col gap-3 relative overflow-hidden"
         style="box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.22);">
        
        <!-- Top Accent Gradient Bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-600"></div>

        <!-- Close / Dismiss Button -->
        <button type="button" id="pwa-dismiss-btn" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 p-1.5 rounded-full hover:bg-slate-100 transition-colors" aria-label="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- App Identity Info -->
        <div class="flex items-center gap-3.5 pr-6">
            <img src="{{ $pwaLogo }}" alt="{{ $pwaSchoolName }}" class="w-12 h-12 rounded-xl object-contain bg-slate-50 border border-slate-100 shadow-sm flex-shrink-0">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <h4 class="font-extrabold text-sm text-slate-900 truncate tracking-tight">{{ $pwaSchoolName }}</h4>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider bg-violet-100 text-violet-700">App</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 leading-snug">
                    Install for fast 1-tap home screen access and instant school alerts.
                </p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-2 pt-1">
            <button type="button" id="pwa-banner-install-btn" 
                    class="flex-1 py-2.5 px-4 rounded-xl text-white font-bold text-xs shadow-md transition-all active:scale-[0.97] flex items-center justify-center gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Install App
            </button>
            <button type="button" id="pwa-not-now-btn" class="py-2.5 px-3 rounded-xl text-slate-500 font-semibold text-xs hover:bg-slate-100 transition-colors">
                Not Now
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     2. FULL SCREEN INTERACTIVE INSTALL MODAL DIALOG
     ══════════════════════════════════════════════════════════════ --}}
<div id="pwa-install-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0 pointer-events-none">
    <div id="pwa-modal-card" class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden transform scale-95 transition-all duration-300 relative">
        
        <!-- Modal Header -->
        <div class="relative bg-gradient-to-br from-violet-600 via-indigo-600 to-slate-900 p-6 text-white text-center overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
            
            <button type="button" onclick="closePwaModal()" class="absolute top-4 right-4 text-white/70 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="mx-auto w-16 h-16 rounded-2xl bg-white p-2 shadow-lg ring-4 ring-white/20 mb-3 flex items-center justify-center">
                <img src="{{ $pwaLogo }}" alt="{{ $pwaSchoolName }}" class="max-h-full max-w-full object-contain">
            </div>

            <h3 class="text-lg font-black tracking-tight leading-snug">{{ $pwaSchoolName }}</h3>
            <p class="text-xs text-white/80 mt-1">School Management & Portal Mobile App</p>
        </div>

        <!-- Modal Body -->
        <div class="p-5 sm:p-6 space-y-5">
            
            <!-- Direct 1-Tap Native Install (shown if browser supports beforeinstallprompt) -->
            <div id="pwa-native-prompt-box" class="hidden">
                <button type="button" id="pwa-direct-install-btn" 
                        class="w-full py-3.5 px-4 rounded-2xl text-white font-extrabold text-sm shadow-lg shadow-violet-500/25 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700">
                    <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Tap Here to Install App</span>
                </button>
                <div class="flex items-center my-4">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Or follow manual steps</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>
            </div>

            <!-- Device Tabs Navigation -->
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

            <!-- TAB 1: Android Instructions -->
            <div id="tab-content-android" class="space-y-3">
                <div class="flex items-start gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <span class="font-bold text-slate-900">Open Browser Menu:</span> Tap the <strong class="text-emerald-700">3 vertical dots (⋮)</strong> in the top-right corner of Chrome or Edge.
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
                        <span class="font-bold text-slate-900">Confirm:</span> Tap <strong class="text-emerald-700">Install</strong>. AcademyHub icon will appear on your phone home screen!
                    </div>
                </div>
            </div>

            <!-- TAB 2: iOS Instructions -->
            <div id="tab-content-ios" class="hidden space-y-3">
                <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <span class="font-bold text-slate-900">Tap Share in Safari:</span> In Apple Safari, tap the <strong class="text-blue-700">Share icon (⎋ with arrow)</strong> at the bottom of the screen.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                    <div>
                        <span class="font-bold text-slate-900">Add to Home Screen:</span> Scroll down the share menu and tap <strong class="text-blue-700">"Add to Home Screen" (⊞)</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                    <div>
                        <span class="font-bold text-slate-900">Tap Add:</span> Tap <strong class="text-blue-700">Add</strong> in the top right corner. The app will be placed on your home screen!
                    </div>
                </div>
            </div>

            <!-- TAB 3: Desktop PC / Mac Instructions -->
            <div id="tab-content-desktop" class="hidden space-y-3">
                <div class="flex items-start gap-3 p-3 bg-violet-50/60 rounded-2xl border border-violet-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-violet-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <span class="font-bold text-slate-900">Address Bar Icon:</span> Look at the far right of your address bar in Chrome/Edge for the <strong class="text-violet-700">Install icon (⊕ or 💻)</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-violet-50/60 rounded-2xl border border-violet-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-violet-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                    <div>
                        <span class="font-bold text-slate-900">Or Browser Menu:</span> Click the <strong class="text-violet-700">3 dots (⋮)</strong> and select <strong class="text-violet-700">"Install {{ $pwaSchoolName }}"</strong>.
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-violet-50/60 rounded-2xl border border-violet-100 text-xs text-slate-700">
                    <div class="w-6 h-6 rounded-full bg-violet-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                    <div>
                        <span class="font-bold text-slate-900">Standalone App:</span> AcademyHub opens in its own fast, dedicated window without browser bars!
                    </div>
                </div>
            </div>

            <!-- Features Highlights -->
            <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-base font-extrabold text-violet-600">⚡ Fast</div>
                    <div class="text-[10px] text-slate-400 font-medium">Instant 1-Tap Load</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-base font-extrabold text-emerald-600">🔔 Alerts</div>
                    <div class="text-[10px] text-slate-400 font-medium">School Notifications</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-base font-extrabold text-indigo-600">📴 Offline</div>
                    <div class="text-[10px] text-slate-400 font-medium">Cached Records</div>
                </div>
            </div>

            <!-- Close Action -->
            <div class="pt-1">
                <button type="button" onclick="closePwaModal()" 
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Got It, Close
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     3. JAVASCRIPT LOGIC
     ══════════════════════════════════════════════════════════════ --}}
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

        // Reveal modal
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

    // Global manual trigger
    window.triggerPwaInstall = function() {
        // If native prompt is active, try it; otherwise open modal instructions
        if (window.deferredPrompt) {
            window.deferredPrompt.prompt();
            window.deferredPrompt.userChoice.then((choice) => {
                if (choice.outcome === 'accepted') {
                    window.hidePwaBanner();
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

    window.hidePwaBanner = function() {
        const banner = document.getElementById('pwa-install-banner');
        if (!banner) return;
        banner.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
        banner.classList.remove('translate-y-0', 'opacity-100');
        sessionStorage.setItem('academyhub_pwa_banner_dismissed', '1');
    };

    function initPwa() {
        // Check if currently running inside standalone / already installed PWA
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) {
            // Hide install triggers if already inside installed PWA
            document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.add('hidden'));
            return;
        }

        // Show install triggers in nav/header
        document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.remove('hidden'));

        const banner = document.getElementById('pwa-install-banner');
        const bannerInstallBtn = document.getElementById('pwa-banner-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');
        const notNowBtn = document.getElementById('pwa-not-now-btn');
        const directInstallBtn = document.getElementById('pwa-direct-install-btn');

        if (dismissBtn) dismissBtn.onclick = () => window.hidePwaBanner();
        if (notNowBtn) notNowBtn.onclick = () => window.hidePwaBanner();

        if (bannerInstallBtn) {
            bannerInstallBtn.onclick = () => {
                window.hidePwaBanner();
                window.triggerPwaInstall();
            };
        }

        if (directInstallBtn) {
            directInstallBtn.onclick = () => {
                if (window.deferredPrompt) {
                    window.deferredPrompt.prompt();
                    window.deferredPrompt.userChoice.then((choice) => {
                        if (choice.outcome === 'accepted') {
                            window.closePwaModal();
                            window.hidePwaBanner();
                        }
                        window.deferredPrompt = null;
                        const nativeBox = document.getElementById('pwa-native-prompt-box');
                        if (nativeBox) nativeBox.classList.add('hidden');
                    });
                }
            };
        }

        // Listen for Chrome beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            const nativeBox = document.getElementById('pwa-native-prompt-box');
            if (nativeBox) nativeBox.classList.remove('hidden');
        });

        // Show floating bottom banner after 2 seconds if not dismissed in this session
        const sessionDismissed = sessionStorage.getItem('academyhub_pwa_banner_dismissed');
        if (!sessionDismissed && banner) {
            setTimeout(() => {
                banner.classList.remove('hidden', 'pointer-events-none', 'translate-y-32', 'opacity-0');
                banner.classList.add('translate-y-0', 'opacity-100');
            }, 2000);
        }
    }

    initPwa();
    document.addEventListener('livewire:navigated', initPwa);
})();
</script>
