@php
    $pwaSchoolName = config('academyhub.school_name');
    if (!$pwaSchoolName && app()->bound('currentTenant') && app('currentTenant')) {
        $pwaSchoolName = app('currentTenant')->name;
    }
    $pwaSchoolName = $pwaSchoolName ?: config('app.name', 'AcademyHub');

    $pwaLogo = null;
    if (config('academyhub.school_logo')) {
        $pwaLogo = asset('uploads/' . str_replace('\\', '/', config('academyhub.school_logo')));
    } elseif (config('academyhub.logo_url')) {
        $pwaLogo = config('academyhub.logo_url');
    } elseif (app()->bound('currentTenant') && app('currentTenant')?->logo) {
        $pwaLogo = asset('uploads/' . str_replace('\\', '/', app('currentTenant')->logo));
    }
    if (!$pwaLogo) {
        $pwaLogo = asset('icons/icon-512.png');
    }

    $pwaColor = config('academyhub.accent_color', '#6366f1');
@endphp

<!-- Native PWA Boot Splash Screen -->
<div id="pwa-app-splash" aria-hidden="true" role="status">
    <style>
        #pwa-app-splash {
            position: fixed;
            inset: 0;
            z-index: 2147483647;
            background: radial-gradient(circle at 50% 38%, #1e293b 0%, #0f172a 60%, #080c14 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Inter", sans-serif;
            color: #ffffff;
            opacity: 1;
            transform: scale(1);
            filter: blur(0px);
            transition: opacity 420ms cubic-bezier(0.16, 1, 0.3, 1), 
                        transform 460ms cubic-bezier(0.16, 1, 0.3, 1), 
                        filter 420ms ease;
            will-change: opacity, transform, filter;
        }
        #pwa-app-splash.pwa-splash-fade-out {
            opacity: 0 !important;
            transform: scale(1.035) !important;
            filter: blur(4px) !important;
            pointer-events: none !important;
        }
        #pwa-app-splash.pwa-splash-hidden {
            display: none !important;
        }
        .pwa-splash-glow {
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.28) 0%, rgba(15, 23, 42, 0) 70%);
            filter: blur(35px);
            pointer-events: none;
            animation: pwa-splash-breathe 2.8s ease-in-out infinite;
        }
        @keyframes pwa-splash-breathe {
            0%, 100% { transform: scale(0.9); opacity: 0.55; }
            50% { transform: scale(1.15); opacity: 0.95; }
        }
        .pwa-splash-center {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            padding: 0 24px;
        }
        .pwa-splash-logo-card {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1.5px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 20px 48px -10px rgba(0, 0, 0, 0.65), 
                        0 0 35px rgba(99, 102, 241, 0.25),
                        inset 0 1px 1px rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            animation: pwa-splash-float 3.2s ease-in-out infinite;
        }
        @keyframes pwa-splash-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        .pwa-splash-logo-card img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 18px;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.3));
        }
        .pwa-splash-title {
            margin-top: 22px;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            text-align: center;
            max-width: 85vw;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.55);
        }
        .pwa-splash-badge {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .pwa-splash-badge .pwa-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }
        .pwa-splash-loader-bar {
            position: relative;
            margin-top: 30px;
            width: 130px;
            height: 3.5px;
            border-radius: 99px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        .pwa-splash-loader-indicator {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 45%;
            border-radius: 99px;
            background: linear-gradient(90deg, #38bdf8, #818cf8, #c084fc);
            box-shadow: 0 0 10px rgba(129, 140, 248, 0.6);
            animation: pwa-splash-slide 1.4s cubic-bezier(0.65, 0, 0.35, 1) infinite;
        }
        @keyframes pwa-splash-slide {
            0% { left: -50%; width: 30%; }
            50% { width: 55%; }
            100% { left: 100%; width: 30%; }
        }
        .pwa-splash-footer {
            position: absolute;
            bottom: 24px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: rgba(148, 163, 184, 0.65);
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 10;
        }
        .pwa-splash-footer svg {
            width: 12px;
            height: 12px;
            color: #10b981;
        }
    </style>

    <div class="pwa-splash-glow"></div>

    <div class="pwa-splash-center">
        <div class="pwa-splash-logo-card">
            <img src="{{ $pwaLogo }}" alt="{{ $pwaSchoolName }}" width="80" height="80" loading="eager" decoding="async">
        </div>
        <div class="pwa-splash-title">{{ $pwaSchoolName }}</div>
        <div class="pwa-splash-badge">
            <span class="pwa-dot"></span>
            <span>School Management Suite</span>
        </div>
        <div class="pwa-splash-loader-bar">
            <div class="pwa-splash-loader-indicator"></div>
        </div>
    </div>

    <div class="pwa-splash-footer">
        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
        <span>Secured • PWA Portal Ready</span>
    </div>
</div>

<script>
(function() {
    const splash = document.getElementById('pwa-app-splash');
    if (!splash) return;

    const startTime = performance.now();
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    // Check if user recently saw splash in this session (e.g. internal navigation)
    // If not standalone and seen within last 2 minutes, dismiss ultra fast (80ms) to keep tab navigation instant
    const lastSeen = sessionStorage.getItem('pwa_splash_timestamp');
    const isRecentInternalNav = lastSeen && (Date.now() - parseInt(lastSeen, 10) < 120000) && !isStandalone;
    const minDisplayDuration = isRecentInternalNav ? 80 : 450; 
    const maxSafetyTimeout = 1200;

    let dismissed = false;

    function dismissSplash() {
        if (dismissed) return;
        dismissed = true;

        try {
            sessionStorage.setItem('pwa_splash_timestamp', Date.now().toString());
        } catch (e) {}

        const elapsed = performance.now() - startTime;
        const remaining = Math.max(0, minDisplayDuration - elapsed);

        setTimeout(function() {
            splash.classList.add('pwa-splash-fade-out');
            setTimeout(function() {
                splash.classList.add('pwa-splash-hidden');
                // Remove from DOM to preserve memory and touch target purity
                if (splash.parentNode) {
                    splash.parentNode.removeChild(splash);
                }
                window.dispatchEvent(new CustomEvent('pwa-splash-dismissed'));
            }, 460);
        }, remaining);
    }

    // Trigger dismissal as soon as document/window is ready
    if (document.readyState === 'complete') {
        dismissSplash();
    } else {
        window.addEventListener('load', dismissSplash, { once: true });
    }

    // Also support Livewire navigated events
    document.addEventListener('livewire:navigated', dismissSplash, { once: true });
    document.addEventListener('livewire:initialized', dismissSplash, { once: true });

    // Hard safety timer: splash never traps user longer than 1.2s regardless of network conditions
    setTimeout(dismissSplash, maxSafetyTimeout);
})();
</script>
