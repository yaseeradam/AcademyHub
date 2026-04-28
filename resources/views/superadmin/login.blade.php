<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { margin:0; font-family: 'Inter', -apple-system, sans-serif; background:#0f172a; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#1e293b; border:1px solid #334155; border-radius:16px; padding:40px; width:100%; max-width:380px; }
        .brand { text-align:center; margin-bottom:32px; }
        .brand-icon { width:52px; height:52px; background:linear-gradient(135deg,#4f46e5,#7c3aed); border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; font-size:22px; color:white; font-weight:900; }
        .brand-title { font-size:18px; font-weight:800; color:#f1f5f9; }
        .brand-sub { font-size:12px; color:#64748b; font-weight:500; margin-top:2px; }
        label { display:block; font-size:12px; font-weight:600; color:#94a3b8; margin-bottom:6px; }
        input { width:100%; padding:10px 14px; background:#0f172a; border:1.5px solid #334155; border-radius:10px; color:#f1f5f9; font-size:14px; outline:none; box-sizing:border-box; transition:border-color .15s; }
        input:focus { border-color:#4f46e5; }
        .field { margin-bottom:18px; }
        .error { font-size:12px; color:#f87171; margin-top:5px; }
        button { width:100%; padding:12px; background:linear-gradient(135deg,#4f46e5,#7c3aed); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; margin-top:8px; transition:opacity .15s; }
        button:hover { opacity:.9; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="brand-icon">✦</div>
            <div class="brand-title">DevConsole</div>
            <div class="brand-sub">Super Admin Access</div>
        </div>

        <form method="POST" action="{{ route('superadmin.login.store') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autofocus required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
