<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin — {{ config('app.name') }}</title>
@vite(['resources/css/app.css'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Galada&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;background:#0a0f1e}
.sa-wrap{display:flex;width:100%;min-height:100vh}
.sa-left{flex:0 0 60%;position:relative;overflow:hidden;background:linear-gradient(135deg,#0a0f1e 0%,#1a1040 40%,#0d1535 100%);display:none}
@media(min-width:1024px){.sa-left{display:flex;flex-direction:column;justify-content:space-between;padding:40px}}
.sa-left-bg{position:absolute;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(99,44,237,.25) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(45,212,191,.12) 0%,transparent 50%),radial-gradient(ellipse at 60% 90%,rgba(59,130,246,.15) 0%,transparent 50%)}
.sa-grid{position:absolute;inset:0;opacity:.07;background-image:linear-gradient(rgba(255,255,255,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.1) 1px,transparent 1px);background-size:60px 60px}
.sa-left-content{position:relative;z-index:1;display:flex;flex-direction:column;justify-content:space-between;height:100%}
.sa-logo{display:flex;align-items:center}
.sa-logo img{height:70px;width:auto;filter:drop-shadow(0 0 20px rgba(139,92,246,.5))}
.sa-hero h2{font-family:'Galada', cursive;font-size:56px;font-weight:normal;color:#fff;line-height:1.25;text-shadow:0 0 40px rgba(139,92,246,.4);margin-bottom:12px}
.sa-hero h2 span{background:linear-gradient(135deg,#a78bfa,#60a5fa);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.sa-hero p{font-size:16px;color:rgba(255,255,255,.7);margin-top:12px;max-width:360px;line-height:1.6}
.sa-accent{width:48px;height:4px;background:linear-gradient(90deg,#8b5cf6,#3b82f6);border-radius:2px;margin-top:16px}
.sa-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.3);border-radius:100px;padding:8px 16px;margin-top:20px}
.sa-badge-dot{width:8px;height:8px;background:#8b5cf6;border-radius:50%;box-shadow:0 0 8px #8b5cf6;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.sa-badge span{font-size:12px;font-weight:600;color:rgba(255,255,255,.8)}
.sa-quote{background:rgba(255,255,255,.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:20px 24px}
.sa-quote .q-icon{font-size:26px;font-weight:900;color:#a78bfa;line-height:1;margin-bottom:6px}
.sa-quote .q-text{font-size:15px;font-weight:600;color:rgba(255,255,255,.9);line-height:1.4}
.sa-quote .q-bar{width:32px;height:3px;background:linear-gradient(90deg,#8b5cf6,#3b82f6);border-radius:2px;margin-top:10px}
.sa-right{flex:1;display:flex;align-items:center;justify-content:center;padding:24px;background:#f8fafc;overflow-y:auto}
.sa-card{width:100%;max-width:420px;background:#fff;border-radius:24px;box-shadow:0 8px 40px rgba(0,0,0,.10);padding:40px 36px 32px}
.sa-icon-wrap{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;box-shadow:0 6px 20px rgba(124,58,237,.35)}
.sa-icon-wrap svg{width:30px;height:30px;color:#fff}
.sa-title{font-family:'Galada', cursive;font-size:34px;font-weight:normal;color:#0f172a;text-align:center;margin-bottom:8px;line-height:1.2}
.sa-subtitle{font-size:14px;color:#64748b;text-align:center;margin-bottom:28px}
.field{margin-bottom:18px}
.field label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px}
.input-wrap{position:relative}
.input-wrap input{width:100%;padding:12px 44px 12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;color:#1e293b;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;font-family:inherit}
.input-wrap input:focus{border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,.12)}
.input-wrap .ico{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#94a3b8}
.err{font-size:12px;color:#dc2626;margin-top:5px;display:flex;align-items:center;gap:4px}
.err-banner{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#dc2626}
.err-banner ul{margin:0;padding-left:16px}
.btn-sa{width:100%;padding:14px;border:none;border-radius:12px;font-size:15px;font-weight:800;color:#fff;cursor:pointer;background:linear-gradient(135deg,#7c3aed,#4f46e5);transition:all .2s;display:flex;align-items:center;justify-content:center;gap:10px;letter-spacing:.3px;margin-top:8px}
.btn-sa:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(124,58,237,.35)}
.sa-footer{text-align:center;margin-top:24px}
.sa-divider{width:40px;height:2px;background:linear-gradient(90deg,#8b5cf6,#4f46e5);border-radius:1px;margin:14px auto 10px}
.sa-tagline{font-size:13px;color:#475569;font-style:italic;text-align:center}
.sa-copyright{font-size:11px;color:#94a3b8;text-align:center;margin-top:8px}
.warning-badge{display:flex;align-items:center;gap:8px;background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;margin-bottom:20px;font-size:12px;color:#92400e;font-weight:600}
</style>
</head>
<body>
<div class="sa-wrap">
  <!-- LEFT -->
  <div class="sa-left">
    <div class="sa-left-bg"></div>
    <div class="sa-grid"></div>
    <div class="sa-left-content">
      <div class="sa-logo">
        <img src="{{ asset('full.png') }}" alt="AcademyHub">
      </div>
      <div class="sa-hero">
        <h2>Super<br><span>Admin</span><br>Portal</h2>
        <p>Manage all schools, tenants, and platform settings from one powerful console.</p>
        <div class="sa-accent"></div>
        <div class="sa-badge">
          <div class="sa-badge-dot"></div>
          <span>Restricted Access — Authorized Personnel Only</span>
        </div>
      </div>
      <div class="sa-quote">
        <div class="q-icon">&ldquo;</div>
        <div class="q-text">Manage the platform, empower every school.</div>
        <div class="q-bar"></div>
      </div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="sa-right">
    <div class="sa-card">
      <div class="sa-icon-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      </div>
      <h1 class="sa-title">Super Admin Login</h1>
      <p class="sa-subtitle">Welcome back! Please sign in to your account.</p>

      <div class="warning-badge">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Restricted: Super Admin access only
      </div>

      @if ($errors->any())
      <div class="err-banner">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      <form method="POST" action="{{ route('superadmin.login.store') }}">
        @csrf
        <div class="field">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <input id="email" name="email" type="email" value="{{ old('email') }}" autofocus required placeholder="superadmin@academyhub.com">
            <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input id="password" name="password" type="password" required placeholder="••••••••">
            <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
        </div>
        <button type="submit" class="btn-sa">
          Sign In to Console
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>
      </form>

      <div class="sa-divider"></div>
      <div class="sa-tagline">Manage the platform, empower every school.</div>
      <div class="sa-copyright">&copy; {{ date('Y') }} AcademyHub. All rights reserved.</div>
    </div>
  </div>
</div>
</body>
</html>
