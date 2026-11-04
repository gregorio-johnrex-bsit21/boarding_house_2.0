<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#080808">
<meta name="color-scheme" content="dark light">
<script>
  (() => {
    try {
      const savedTheme = localStorage.getItem('boardershub-theme');
      document.documentElement.dataset.theme = savedTheme === 'light' ? 'light' : 'dark';
    } catch (error) {
      if (error.name !== 'SecurityError') throw error;
      document.documentElement.dataset.theme = 'dark';
    }
  })();
</script>
<style>html{background:#080808;color-scheme:dark}@media(prefers-color-scheme:light){html:not([data-theme]){background:#e4e9e8;color-scheme:light}}html[data-theme="light"]{background:#e4e9e8;color-scheme:light}</style>
<title>Choose a new password · BoardersHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/portal-v2.css') }}?v={{ filemtime(public_path('css/portal-v2.css')) }}">
</head>
<body>
<section class="auth" id="forgot">
  <div class="bgb" aria-hidden="true"><i class="b1"></i><i class="b2"></i></div>
  <form class="acard sm" id="reset-password-form" method="POST" action="{{ route('password.store') }}" data-v2-validate>
    @csrf
    <div class="ar" style="grid-column:1/-1">
      <div class="auth-brand-row">
        <div class="mk"><i><svg class="i" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8M5 10v10h14V10"/></svg></i>BoardersHub <span class="cap" style="font-weight:400">· Boarder Dashboard</span></div>
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
          <svg data-theme-icon="light" aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
          <svg data-theme-icon="dark" aria-hidden="true" viewBox="0 0 24 24"><path d="M20.5 15.5A8.5 8.5 0 018.5 3.5 8.5 8.5 0 1020.5 15.5z"/></svg>
          <span data-theme-label>Light</span>
        </button>
      </div>
      <div>
        <p class="lbl">Secure your account</p>
        <h1 style="font-size:30px;margin:4px 0">Choose a new password</h1>
        <p class="sub">Set a new password for your boarder account.</p>
      </div>
      <input type="hidden" name="token" value="{{ $request->route('token') }}">
      <div>
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="email" required autofocus aria-describedby="email-error" @error('email') aria-invalid="true" @enderror>
        @error('email')<p class="er-t" id="email-error" role="alert">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="password">New password</label>
        <div class="pw">
          <input id="password" type="password" name="password" autocomplete="new-password" required aria-describedby="password-error" @error('password') aria-invalid="true" @enderror>
          <button type="button" data-v2-password-toggle aria-label="Show new password" aria-pressed="false">Show</button>
        </div>
        @error('password')<p class="er-t" id="password-error" role="alert">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="password_confirmation">Confirm new password</label>
        <div class="pw">
          <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required aria-describedby="password-confirmation-error" @error('password_confirmation') aria-invalid="true" @enderror>
          <button type="button" data-v2-password-toggle aria-label="Show confirmation password" aria-pressed="false">Show</button>
        </div>
        @error('password_confirmation')<p class="er-t" id="password-confirmation-error" role="alert">{{ $message }}</p>@enderror
      </div>
      @if($errors->any() && ! $errors->has('email') && ! $errors->has('password') && ! $errors->has('password_confirmation'))<p class="er-t" role="alert">{{ $errors->first() }}</p>@endif
      <button type="submit" class="btn" style="min-height:52px">Update password</button>
      <a class="btn g s" href="{{ route('login') }}">← Back to sign in</a>
    </div>
  </form>
</section>
<div class="toast" id="tt" role="status" aria-live="polite">@if(session('status')){{ session('status') }}@endif</div>
<script defer src="{{ asset('js/theme-toggle.js') }}?v={{ filemtime(public_path('js/theme-toggle.js')) }}"></script>
<script defer src="{{ asset('js/portal-v2.js') }}?v={{ filemtime(public_path('js/portal-v2.js')) }}"></script>
</body>
</html>
