<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Offline — {{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <meta name="theme-color" content="{{ config('academyhub.accent_color', '#7c3aed') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: max(20px, env(safe-area-inset-top)) max(20px, env(safe-area-inset-right)) max(20px, env(safe-area-inset-bottom)) max(20px, env(safe-area-inset-left));
            overscroll-behavior-y: contain;
        }
        .offline-card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(226, 232, 240, 0.8);
            width: 100%;
            max-width: 420px;
            padding: 36px 28px;
            text-align: center;
        }
        .logo-wrap {
            margin-bottom: 24px;
        }
        .school-logo {
            height: 52px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
            margin: 0 auto;
        }
        .brand-fallback {
            font-size: 20px;
            font-weight: 900;
            color: #1e293b;
            letter-spacing: -0.02em;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: #fef2f2;
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }
        .icon-circle::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 30px;
            border: 2px dashed rgba(239, 68, 68, 0.3);
            animation: pulse-ring 2.5s infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.05); opacity: 0.3; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .icon-circle svg {
            width: 38px;
            height: 38px;
        }
        h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .btn-retry {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 20px;
            border-radius: 16px;
            background: {{ config('academyhub.accent_color', '#7c3aed') }};
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(124, 58, 237, 0.25);
            transition: all 0.15s ease;
        }
        .btn-retry:active {
            transform: scale(0.97);
        }
        .tips-box {
            margin-top: 24px;
            padding: 14px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            text-align: left;
            font-size: 12px;
            color: #475569;
        }
        .tips-box ul {
            padding-left: 18px;
            margin-top: 6px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="logo-wrap">
            <img src="{{ config('academyhub.logo_url') ?: '/full.png' }}" 
                 alt="{{ config('academyhub.school_name', 'AcademyHub') }}" 
                 class="school-logo"
                 onerror="this.style.display='none';document.getElementById('brand-title-fallback').style.display='block';">
            <div id="brand-title-fallback" style="display:none;" class="brand-fallback">
                {{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}
            </div>
        </div>

        <div class="icon-circle">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-1.414-1.414m-1.414-1.414L3 3m5.657 5.657a5 5 0 00-.707.707m-1.414 1.414a9 9 0 00-1.414 1.414m8.485-8.485a5 5 0 017.071 0" />
            </svg>
        </div>

        <h1>Connection Lost</h1>
        <p>You are currently offline. Previously opened pages remain cached in your app. Check your connection and tap below.</p>

        <button type="button" class="btn-retry" id="retry-btn" onclick="checkConnectionAndRetry()">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span id="retry-text">Check Connection &amp; Retry</span>
        </button>

        <div class="tips-box">
            <strong>Offline App Mode:</strong>
            <ul>
                <li>Previously visited dashboards and records are cached and readable.</li>
                <li>When internet connectivity is restored, this app reloads automatically.</li>
            </ul>
        </div>
    </div>

    <script>
        function checkConnectionAndRetry() {
            const btnText = document.getElementById('retry-text');
            btnText.textContent = 'Checking connection...';
            if (navigator.onLine) {
                window.location.reload();
            } else {
                fetch('/offline', { method: 'HEAD', cache: 'no-store' })
                    .then(() => window.location.reload())
                    .catch(() => {
                        setTimeout(() => {
                            btnText.textContent = 'Still offline — Tap to retry';
                        }, 800);
                    });
            }
        }

        // Auto-reload when back online
        window.addEventListener('online', () => {
            const btnText = document.getElementById('retry-text');
            if (btnText) btnText.textContent = 'Online! Reconnecting...';
            setTimeout(() => window.location.reload(), 600);
        });
    </script>
</body>
</html>
