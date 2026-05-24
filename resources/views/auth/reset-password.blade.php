<x-layouts.guest>
<style>
.reset-wrap{display:flex;min-height:100vh;align-items:center;justify-content:center;background:#f8fafc;font-family:'Inter',sans-serif;padding:24px}
.reset-card{width:100%;max-width:440px;background:#fff;border-radius:24px;box-shadow:0 8px 40px rgba(0,0,0,.10);padding:40px 36px 32px}
.reset-title{font-family:'Galada',cursive;font-size:28px;color:#0f172a;text-align:center;margin-bottom:8px}
.reset-sub{font-size:14px;color:#64748b;text-align:center;margin-bottom:24px;line-height:1.5}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px}
.input-wrap{position:relative}
.input-wrap input{width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;color:#1e293b;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;font-family:inherit;box-sizing:border-box}
.input-wrap input:focus{border-color:#E78B2C;box-shadow:0 0 0 3px rgba(231,139,44,.12)}
.btn-reset{width:100%;padding:14px;border:none;border-radius:12px;font-size:15px;font-weight:800;color:#fff;background:#E78B2C;cursor:pointer;transition:all .2s;letter-spacing:.3px}
.btn-reset:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,0,0,.18)}
.error-msg{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#dc2626}
.error-msg ul{margin:0;padding-left:16px}
.back-link{display:block;text-align:center;margin-top:20px;font-size:13px;color:#64748b}
.back-link a{color:#E78B2C;font-weight:700;text-decoration:none}
</style>

<div class="reset-wrap">
  <div class="reset-card">
    <h1 class="reset-title">Reset Password</h1>
    <p class="reset-sub">Enter your new password below.</p>

    @if ($errors->any())
      <div class="error-msg">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $request->route('token') }}">

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-wrap">
          <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">New Password</label>
        <div class="input-wrap">
          <input id="password" name="password" type="password" required placeholder="••••••••">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm Password</label>
        <div class="input-wrap">
          <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="••••••••">
        </div>
      </div>

      <button type="submit" class="btn-reset">Reset Password</button>
    </form>

    <div class="back-link"><a href="{{ route('login') }}">Back to Login</a></div>
  </div>
</div>
</x-layouts.guest>
