<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Server Error</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            color: #e2e8f0;
            overflow: hidden;
        }
        .container { text-align: center; padding: 2rem; max-width: 480px; }
        .code {
            font-size: 8rem; font-weight: 900; line-height: 1;
            background: linear-gradient(135deg, #f97316, #ef4444);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            letter-spacing: -0.05em;
        }
        .title { font-size: 1.5rem; font-weight: 700; margin-top: 0.5rem; color: #f1f5f9; }
        .message { margin-top: 1rem; font-size: 0.95rem; color: #94a3b8; line-height: 1.6; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 2rem;
            padding: 0.75rem 1.75rem; border-radius: 0.75rem;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff; font-weight: 700; font-size: 0.875rem; text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4); }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; }
        .orb-1 { width: 400px; height: 400px; background: #f97316; top: -100px; right: -100px; }
        .orb-2 { width: 300px; height: 300px; background: #dc2626; bottom: -80px; left: -80px; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="container">
        <div class="code">500</div>
        <h1 class="title">Something Went Wrong</h1>
        <p class="message">
            We're experiencing a temporary issue on our end.
            Please try again in a few moments. If the problem persists, contact your school's IT administrator.
        </p>
        <a href="/" class="btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Try Again
        </a>
    </div>
</body>
</html>
