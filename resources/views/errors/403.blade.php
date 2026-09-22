<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access Denied</title>
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
            background: linear-gradient(135deg, #ef4444, #dc2626);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            letter-spacing: -0.05em;
        }
        .title { font-size: 1.5rem; font-weight: 700; margin-top: 0.5rem; color: #f1f5f9; }
        .message { margin-top: 1rem; font-size: 0.95rem; color: #94a3b8; line-height: 1.6; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 2rem;
            padding: 0.75rem 1.75rem; border-radius: 0.75rem;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff; font-weight: 700; font-size: 0.875rem; text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; }
        .orb-1 { width: 400px; height: 400px; background: #ef4444; top: -100px; right: -100px; }
        .orb-2 { width: 300px; height: 300px; background: #8b5cf6; bottom: -80px; left: -80px; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="container">
        <div class="code">403</div>
        <h1 class="title">Access Denied</h1>
        <p class="message">
            You don't have permission to access this page.
            If you believe this is an error, please contact your school administrator.
        </p>
        <a href="/" class="btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>
</body>
</html>
