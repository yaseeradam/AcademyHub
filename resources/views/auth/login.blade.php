<x-layouts.guest>
<style>
*{box-sizing:border-box;margin:0;padding:0}
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Galada&display=swap');
.login-wrap{display:flex;min-height:100vh;font-family:'Inter',sans-serif}
.login-left{position:relative;flex:0 0 60%;overflow:hidden;display:none}
@media(min-width:1024px){.login-left{display:block}}
.carousel-slide{position:absolute;inset:0;opacity:0;transition:opacity 1.2s cubic-bezier(.4,0,.2,1)}
.carousel-slide.active{opacity:1}
.carousel-slide img{width:100%;height:100%;object-fit:cover}
.left-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,20,60,.45) 0%,rgba(0,0,0,.1) 50%,rgba(0,0,0,.5) 100%)}
.left-content{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:space-between;padding:40px}
.left-logo{display:flex;align-items:center;gap:14px}
.left-logo img{height:73px;width:auto;filter:drop-shadow(0 2px 10px rgba(0,0,0,.35))}
.left-logo-text{color:#fff}
.left-logo-text .name{font-size:20px;font-weight:900;line-height:1.2;text-shadow:0 1px 6px rgba(0,0,0,.3)}
.left-logo-text .sub{font-size:12px;font-weight:500;opacity:.85;margin-top:3px}
.left-hero{color:#fff}
.left-hero h2{font-family:'Galada', cursive;font-size:74px;font-weight:normal;line-height:1.15;color:#fff;text-shadow:0 4px 24px rgba(0,0,0,.55),0 1px 0 rgba(0,0,0,.4);letter-spacing:0px;margin-bottom:8px}
.left-hero p{font-size:16px;font-weight:500;color:rgba(255,255,255,.92);margin-top:12px;max-width:340px;line-height:1.6;text-shadow:0 1px 6px rgba(0,0,0,.4)}
.left-accent{width:52px;height:4px;border-radius:2px;margin-top:16px}
.left-quote{background:rgba(255,255,255,.95);border-radius:16px;padding:20px 24px;max-width:300px;backdrop-filter:blur(8px)}
.left-quote .q-icon{font-size:28px;line-height:1;font-weight:900;margin-bottom:6px}
.left-quote .q-text{font-size:15px;font-weight:700;color:#1e293b;line-height:1.4}
.left-quote .q-bar{width:32px;height:3px;border-radius:2px;margin-top:10px}
.login-right{flex:1;display:flex;align-items:center;justify-content:center;padding:24px;background:#f8fafc;overflow-y:auto}
.login-card{width:100%;max-width:440px;background:#fff;border-radius:24px;box-shadow:0 8px 40px rgba(0,0,0,.10);padding:40px 36px 32px;position:relative}
.role-tabs{display:flex;gap:6px;background:#f1f5f9;border-radius:12px;padding:5px;margin-bottom:28px}
.role-tab{flex:1;padding:8px 6px;border:none;background:transparent;border-radius:9px;font-size:12px;font-weight:700;color:#64748b;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:5px}
.role-tab.active{background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.10);color:#1e293b}
.role-icon-wrap{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;border:2px solid}
.card-title{font-family:'Galada', cursive;font-size:32px;font-weight:normal;color:#0f172a;text-align:center;margin-bottom:8px;line-height:1.2}
.card-sub{font-size:14px;color:#64748b;text-align:center;margin-bottom:24px}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px}
.form-label-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
.form-label-row .forgot{font-size:12px;font-weight:600;text-decoration:none}
.input-wrap{position:relative}
.input-wrap input{width:100%;padding:12px 44px 12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;color:#1e293b;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;font-family:inherit}
.input-wrap input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-light)}
.input-wrap .input-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#94a3b8}
.remember{display:flex;align-items:center;gap:8px;margin-bottom:22px;font-size:13px;color:#475569;font-weight:500;cursor:pointer}
.remember input[type=checkbox]{width:16px;height:16px;accent-color:var(--accent);cursor:pointer}
.btn-login{width:100%;padding:14px;border:none;border-radius:12px;font-size:15px;font-weight:800;color:#fff;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:10px;letter-spacing:.3px}
.btn-login:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,0,0,.18)}
.btn-login svg{width:20px;height:20px}
.card-footer{text-align:center;margin-top:20px;font-size:13px;color:#64748b}
.card-footer a{font-weight:700;text-decoration:none}
.card-divider{width:40px;height:2px;border-radius:1px;margin:16px auto 12px}
.card-tagline{text-align:center;font-size:13px;color:#475569;font-weight:500;font-style:italic}
.card-copyright{text-align:center;font-size:11px;color:#94a3b8;margin-top:10px}
.login-form-section{display:none}
.login-form-section.active{display:block;animation:formin .3s ease}
@keyframes formin{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.error-banner{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#dc2626}
.error-banner ul{margin:0;padding-left:16px}
</style>

<div class="login-wrap">
  <!-- LEFT PANEL -->
  <div class="login-left">
    <!-- Role-based Background Images -->
    <div class="carousel-slide active" id="slide-staff"><img src="{{ asset('login-assets/staff.jpg') }}" alt="Staff Background"></div>
    <div class="carousel-slide" id="slide-parent"><img src="{{ asset('login-assets/parent.jpg') }}" alt="Parent Background"></div>
    <div class="carousel-slide" id="slide-student"><img src="{{ asset('login-assets/student.jpg') }}" alt="Student Background"></div>
    <div class="left-overlay"></div>

    <div class="left-content">
      <!-- Logo -->
      <div class="left-logo">
        <img src="{{ asset('full.png') }}" alt="AcademyHub Logo">
        <div class="left-logo-text">
          <div class="name">{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}</div>
          <div class="sub">Smart Learning System</div>
        </div>
      </div>

      <!-- Hero text -->
      <div class="left-hero">
        <h2>Welcome Back!</h2>
        <p id="left-desc">Access the admin portal and manage your school with efficiency.</p>
        <div class="left-accent" id="left-accent" style="background:#E78B2C"></div>
      </div>

      <!-- Quote -->
      <div>
        <div class="left-quote">
          <div class="q-icon" id="left-q-icon" style="color:#E78B2C">&ldquo;</div>
          <div class="q-text" id="left-q-text">Lead with vision, manage with purpose.</div>
          <div class="q-bar" id="left-q-bar" style="background:#E78B2C"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="login-right">
    <div class="login-card" style="--accent:#E78B2C;--accent-light:rgba(231,139,44,.12)">

      <!-- Tabs -->
      <div class="role-tabs" id="role-tabs">
        <button class="role-tab active" id="tab-staff" onclick="switchRole('staff')">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          Staff
        </button>
        <button class="role-tab" id="tab-parent" onclick="switchRole('parent')">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Parent
        </button>
        <button class="role-tab" id="tab-student" onclick="switchRole('student')">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
          Student
        </button>
      </div>

      <!-- Role icon removed -->

      <h1 class="card-title" id="card-title">Staff Login</h1>
      <p class="card-sub">Welcome back! Please sign in to your account.</p>

      @if ($errors->any())
      <div class="error-banner" style="background:#fef2f2;border:1.5px solid #f87171;border-radius:12px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#b91c1c;display:flex;align-items:flex-start;gap:10px;">
        <svg style="width:20px;height:20px;flex-shrink:0;margin-top:1px;color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div><ul style="margin:0;padding-left:16px;line-height:1.6;">@foreach ($errors->all() as $e)<li style="font-weight:600;">{{ $e }}</li>@endforeach</ul></div>
      </div>
      @endif

      @if(session('status'))
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#92400e">{{ session('status') }}</div>
      @endif

      @if(session('warning'))
      <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:12px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#c2410c;display:flex;align-items:flex-start;gap:10px;">
        <svg style="width:20px;height:20px;flex-shrink:0;margin-top:1px;color:#f97316;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div style="font-weight:600;">{{ session('warning') }}</div>
      </div>
      @endif

      @if(session('error'))
      <div style="background:#fef2f2;border:1.5px solid #f87171;border-radius:12px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#b91c1c;display:flex;align-items:flex-start;gap:10px;">
        <svg style="width:20px;height:20px;flex-shrink:0;margin-top:1px;color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div style="font-weight:600;">{{ session('error') }}</div>
      </div>
      @endif

      <!-- STAFF FORM -->
      <div id="form-staff" class="login-form-section active">
        <form method="POST" action="/login">
          @csrf
          <input type="hidden" name="login_type" value="staff">
          <div class="form-group">
            <label class="form-label" for="staff-email">Email Address</label>
            <div class="input-wrap">
              <input id="staff-email" name="email" type="email" value="{{ old('email') }}" required placeholder="admin@school.com">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            @error('email')
              <div style="color:#ef4444; font-size:12px; margin-top:6px; font-weight:600; display:flex; align-items:center; gap:4px;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          <div class="form-group">
            <div class="form-label-row">
              <span class="form-label" style="margin:0">Password</span>
              <a href="{{ route('password.request') }}" class="forgot" style="color:#E78B2C">Forgot password?</a>
            </div>
            <div class="input-wrap">
              <input id="staff-password" name="password" type="password" required placeholder="••••••••">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            @error('password')
              <div style="color:#ef4444; font-size:12px; margin-top:6px; font-weight:600; display:flex; align-items:center; gap:4px;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
          <button type="submit" class="btn-login" style="background:#E78B2C">
            Login
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </button>
        </form>
      </div>

      <!-- PARENT FORM -->
      <div id="form-parent" class="login-form-section">
        <form method="POST" action="/login">
          @csrf
          <input type="hidden" name="login_type" value="parent">
          <div class="form-group">
            <label class="form-label" for="parent-email">Email Address</label>
            <div class="input-wrap">
              <input id="parent-email" name="email" type="email" value="{{ old('email') }}" required placeholder="parent@email.com">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            @error('email')
              <div style="color:#ef4444; font-size:12px; margin-top:6px; font-weight:600; display:flex; align-items:center; gap:4px;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          <div class="form-group">
            <div class="form-label-row">
              <span class="form-label" style="margin:0">Password</span>
              <a href="{{ route('password.request') }}" class="forgot" style="color:#7C3AED">Forgot password?</a>
            </div>
            <div class="input-wrap">
              <input id="parent-password" name="password" type="password" required placeholder="••••••••">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            @error('password')
              <div style="color:#ef4444; font-size:12px; margin-top:6px; font-weight:600; display:flex; align-items:center; gap:4px;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
          <button type="submit" class="btn-login" style="background:#7C3AED">
            Login
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </button>
        </form>
      </div>

      <!-- STUDENT FORM -->
      <div id="form-student" class="login-form-section">
        <form method="POST" action="/login">
          @csrf
          <input type="hidden" name="login_type" value="student">
          <div class="form-group">
            <label class="form-label" for="student-admission">Admission Number</label>
            <div class="input-wrap">
              <input id="student-admission" name="admission_number" type="text" value="{{ old('admission_number') }}" required placeholder="STU20240001">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
            </div>
            @error('admission_number')
              <div style="color:#ef4444; font-size:12px; margin-top:6px; font-weight:600; display:flex; align-items:center; gap:4px;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          <div class="form-group">
            <div class="form-label-row">
              <span class="form-label" style="margin:0">Password</span>
              <a href="{{ route('password.request') }}" class="forgot" style="color:#1D4ED8">Forgot password?</a>
            </div>
            <div class="input-wrap">
              <input id="student-password" name="password" type="password" required placeholder="••••••••">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            @error('password')
              <div style="color:#ef4444; font-size:12px; margin-top:6px; font-weight:600; display:flex; align-items:center; gap:4px;">
                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
          <button type="submit" class="btn-login" style="background:#1D4ED8">
            Login
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </button>
        </form>
      </div>

      <div class="card-footer">
        Don't have an account? <a href="#" id="contact-link" style="color:#E78B2C">Contact your administrator</a>
      </div>
      <div class="card-divider" id="card-divider" style="background:#E78B2C"></div>
      <div class="card-tagline" id="card-tagline">Lead with vision, manage with purpose.</div>
      <div class="card-copyright">&copy; {{ date('Y') }} {{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}. All rights reserved.</div>
    </div>
  </div>
</div>

<script>
const roles = {
  staff: {
    accent: '#E78B2C', accentLight: 'rgba(231,139,44,.12)',
    iconBg: '#FFF7ED', iconBorder: '#FDDCB0', iconStroke: '#E78B2C',
    iconPath: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    title: 'Staff Login', desc: 'Access the admin portal and manage your school with efficiency.',
    quote: 'Lead with vision, manage with purpose.', tagline: 'Lead with vision, manage with purpose.',
    contactColor: '#E78B2C', dividerColor: '#E78B2C', leftAccent: '#E78B2C'
  },
  parent: {
    accent: '#7C3AED', accentLight: 'rgba(124,58,237,.12)',
    iconBg: '#F5F3FF', iconBorder: '#DDD6FE', iconStroke: '#7C3AED',
    iconPath: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    title: 'Parent Login', desc: 'Access your school portal and stay connected with your child\'s education.',
    quote: 'Together, we nurture growth, every day.', tagline: 'Together, we nurture growth, every day.',
    contactColor: '#7C3AED', dividerColor: '#7C3AED', leftAccent: '#7C3AED'
  },
  student: {
    accent: '#1D4ED8', accentLight: 'rgba(29,78,216,.12)',
    iconBg: '#EFF6FF', iconBorder: '#BFDBFE', iconStroke: '#1D4ED8',
    iconPath: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
    title: 'Student Login', desc: 'Access your school portal and continue your learning journey.',
    quote: 'Learn today, lead tomorrow.', tagline: 'Learn today, lead tomorrow.',
    contactColor: '#1D4ED8', dividerColor: '#1D4ED8', leftAccent: '#1D4ED8'
  }
};

function switchRole(role) {
  const r = roles[role];
  // Tabs
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + role).classList.add('active');
  // Forms
  document.querySelectorAll('.login-form-section').forEach(f => f.classList.remove('active'));
  document.getElementById('form-' + role).classList.add('active');
  // Card accent
  const card = document.querySelector('.login-card');
  card.style.setProperty('--accent', r.accent);
  card.style.setProperty('--accent-light', r.accentLight);
  // Icon logic removed
  // Title
  document.getElementById('card-title').textContent = r.title;
  // Footer
  document.getElementById('contact-link').style.color = r.contactColor;
  document.getElementById('card-divider').style.background = r.dividerColor;
  document.getElementById('card-tagline').textContent = r.tagline;
  // Left panel
  document.getElementById('left-desc').textContent = r.desc;
  document.getElementById('left-accent').style.background = r.leftAccent;
  document.getElementById('left-q-icon').style.color = r.leftAccent;
  document.getElementById('left-q-text').textContent = r.quote;
  document.getElementById('left-q-bar').style.background = r.leftAccent;
  // Dynamic Background Image Change
  document.querySelectorAll('.carousel-slide').forEach(s => s.classList.remove('active'));
  const targetSlide = document.getElementById('slide-' + role);
  if (targetSlide) {
    targetSlide.classList.add('active');
  }
}

// Init
const oldLoginType = "{{ old('login_type', 'staff') }}";
switchRole(oldLoginType);
</script>
</x-layouts.guest>
