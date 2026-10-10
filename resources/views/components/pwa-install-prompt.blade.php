@php
    $pwaSchoolName = config('academyhub.school_name');
    if (!$pwaSchoolName && app()->bound('currentTenant') && app('currentTenant')) {
        $pwaSchoolName = app('currentTenant')->name;
    }
    $pwaSchoolName = $pwaSchoolName ?: config('app.name', 'AcademyHub');
    $pwaLogo = config('academyhub.logo_url') ?: asset('icons/icon-192.png');
    $pwaColor = config('academyhub.accent_color', '#7c3aed');
@endphp

<div id="pwa-install-banner" class="hidden fixed bottom-4 right-4 left-4 sm:left-auto z-50 transition-all duration-500 transform translate-y-32 opacity-0 pointer-events-none max-w-sm sm:w-96">
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/80 p-4 sm:p-5 flex flex-col gap-3 relative overflow-hidden"
         style="box-shadow: 0 12px 36px -6px rgba(15, 23, 42, 0.18);">
        
        <!-- Top Accent Bar -->
        <div class="absolute top-0 left-0 right-0 h-1" style="background: {{ $pwaColor }};"></div>

        <!-- Close Button -->
        <button type="button" id="pwa-dismiss-btn" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 p-1.5 rounded-full hover:bg-slate-100 transition-colors" aria-label="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- Content Row -->
        <div class="flex items-center gap-3.5 pr-6">
            <img src="{{ $pwaLogo }}" alt="{{ $pwaSchoolName }}" class="w-12 h-12 rounded-xl object-contain bg-slate-50 border border-slate-100 shadow-sm flex-shrink-0">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <h4 class="font-extrabold text-sm text-slate-900 truncate tracking-tight">{{ $pwaSchoolName }}</h4>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider bg-violet-50 text-violet-700">App</span>
                </div>
                <p id="pwa-prompt-desc" class="text-xs text-slate-500 mt-0.5 leading-snug">
                    Install for fast 1-tap desktop/home screen access and instant school alerts.
                </p>
            </div>
        </div>

        <!-- Action Section: Install Button -->
        <div id="pwa-android-actions" class="flex items-center gap-2 pt-1">
            <button type="button" id="pwa-install-btn" 
                    class="flex-1 py-2.5 px-4 rounded-xl text-white font-bold text-xs shadow-md transition-all active:scale-[0.97] flex items-center justify-center gap-2"
                    style="background: {{ $pwaColor }};">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Install App
            </button>
            <button type="button" id="pwa-not-now-btn" class="py-2.5 px-3 rounded-xl text-slate-500 font-semibold text-xs hover:bg-slate-100 transition-colors">
                Not Now
            </button>
        </div>

        <!-- Action Section: iOS Safari Instructions (shown only on iOS Safari) -->
        <div id="pwa-ios-instructions" class="hidden text-xs text-slate-600 bg-slate-50 rounded-xl p-3 border border-slate-100 mt-1">
            <div class="font-bold text-slate-800 flex items-center gap-1.5 mb-1">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.61-.75 1.04-1.8 0.92-2.85-.92.04-2.06.63-2.73 1.41-.59.69-1.11 1.76-.97 2.81 1.03.08 2.08-.55 2.78-1.37z"/></svg>
                Install on your iPhone / iPad:
            </div>
            <ol class="list-decimal list-inside space-y-1 text-slate-500">
                <li>Tap the <span class="font-bold text-slate-700">Share</span> button <span class="inline-block px-1 bg-slate-200 rounded text-slate-700 font-mono">⎋</span> in Safari.</li>
                <li>Scroll down and tap <span class="font-bold text-slate-700">Add to Home Screen</span> <span class="inline-block px-1 bg-slate-200 rounded text-slate-700 font-mono">⊞</span>.</li>
            </ol>
        </div>
    </div>
</div>

<script>
(function() {
    function initPwaPrompt() {
        // If already in standalone/PWA installed mode, never display prompt
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) {
            document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.add('hidden'));
            return;
        }

        const banner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');
        const notNowBtn = document.getElementById('pwa-not-now-btn');
        const androidActions = document.getElementById('pwa-android-actions');
        const iosInstructions = document.getElementById('pwa-ios-instructions');

        const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

        function showBanner(force = false) {
            if (!banner) return;
            if (!force) {
                const dismissedUntil = localStorage.getItem('academyhub_pwa_dismissed');
                if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
                    return;
                }
            }
            banner.classList.remove('hidden', 'pointer-events-none', 'translate-y-32', 'opacity-0');
            banner.classList.add('translate-y-0', 'opacity-100');
        }

        function hideBanner(cooldownDays = 1) {
            if (!banner) return;
            banner.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
            banner.classList.remove('translate-y-0', 'opacity-100');
            localStorage.setItem('academyhub_pwa_dismissed', (Date.now() + cooldownDays * 86400 * 1000).toString());
        }

        if (dismissBtn) dismissBtn.onclick = () => hideBanner(2);
        if (notNowBtn) notNowBtn.onclick = () => hideBanner(1);

        // Global manual install trigger (e.g. from sidebar or settings menu)
        window.triggerPwaInstall = function() {
            localStorage.removeItem('academyhub_pwa_dismissed');
            if (window.deferredPrompt) {
                window.deferredPrompt.prompt();
                window.deferredPrompt.userChoice.then((choice) => {
                    if (choice.outcome === 'accepted') {
                        hideBanner(30);
                    }
                    window.deferredPrompt = null;
                });
            } else if (isIos) {
                if (androidActions) androidActions.classList.add('hidden');
                if (iosInstructions) iosInstructions.classList.remove('hidden');
                showBanner(true);
            } else {
                showBanner(true);
            }
        };

        // Listen for browser's beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.remove('hidden'));
            setTimeout(() => showBanner(false), 2000);
        });

        // Also check if deferredPrompt was already captured globally
        if (window.deferredPrompt) {
            document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.remove('hidden'));
            setTimeout(() => showBanner(false), 1500);
        } else if (isIos && !window.navigator.standalone) {
            if (androidActions) androidActions.classList.add('hidden');
            if (iosInstructions) iosInstructions.classList.remove('hidden');
            setTimeout(() => showBanner(false), 2500);
        }

        if (installBtn) {
            installBtn.onclick = async () => {
                if (window.deferredPrompt) {
                    window.deferredPrompt.prompt();
                    const choice = await window.deferredPrompt.userChoice;
                    if (choice.outcome === 'accepted') {
                        hideBanner(30);
                    }
                    window.deferredPrompt = null;
                } else if (isIos) {
                    if (androidActions) androidActions.classList.add('hidden');
                    if (iosInstructions) iosInstructions.classList.remove('hidden');
                } else {
                    alert('To install AcademyHub app: Click the 3 dots menu (⋮) in your browser address bar and select "Install App" or "Add to Home Screen".');
                }
            };
        }
    }

    // Init on first load and on Livewire page transitions
    initPwaPrompt();
    document.addEventListener('livewire:navigated', initPwaPrompt);
})();
</script>
