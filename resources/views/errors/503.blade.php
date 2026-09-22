<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 — Maintenance Mode</title>
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
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            letter-spacing: -0.05em;
        }
        .title { font-size: 1.5rem; font-weight: 700; margin-top: 0.5rem; color: #f1f5f9; }
        .message { margin-top: 1rem; font-size: 0.95rem; color: #94a3b8; line-height: 1.6; }
        .spinner {
            margin: 2rem auto 0;
            width: 40px; height: 40px;
            border: 3px solid rgba(139, 92, 246, 0.2);
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .hint { margin-top: 1rem; font-size: 0.8rem; color: #64748b; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; }
        .orb-1 { width: 400px; height: 400px; background: #8b5cf6; top: -100px; right: -100px; }
        .orb-2 { width: 300px; height: 300px; background: #6366f1; bottom: -80px; left: -80px; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="container">
        <div class="code">503</div>
        <h1 class="title">Under Maintenance</h1>
        <p class="message">
            We're performing scheduled maintenance to improve your experience.
            The system will be back online shortly.
        </p>
        <div class="spinner"></div>
        <p class="hint">This page will automatically refresh when the system is ready.</p>
    </div>
    <script>setTimeout(() => location.reload(), 30000);</script>
</body>
</html>
