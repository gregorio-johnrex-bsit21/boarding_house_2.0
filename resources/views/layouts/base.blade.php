@php
  $residentPage = request()->routeIs('tenant.*', 'profile.edit');
  $boarderThemePage = request()->routeIs('tenant.*')
      || (request()->routeIs('profile.edit') && auth()->user()?->role !== 'admin');
  $portalV2 = trim($__env->yieldContent('portal-v2')) === 'enabled';
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full {{ $residentPage ? 'tenant-theme' : 'bg-slate-50' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'BoardersHub')</title>
@if($boarderThemePage)
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
@endif
<style>
  [x-cloak]{display:none!important}
  body{background:#f3f5f8;font-family:'Figtree',system-ui,sans-serif}
  @if($boarderThemePage)
  html.tenant-theme,body.tenant-theme{background:#080808!important;color-scheme:dark}
  html.tenant-theme[data-theme="light"],html.tenant-theme[data-theme="light"] body.tenant-theme{background:#e4e9e8!important;color-scheme:light}
  @media(prefers-color-scheme:light){html.tenant-theme:not([data-theme]),html.tenant-theme:not([data-theme]) body.tenant-theme{background:#e4e9e8!important;color-scheme:light}}
  @endif
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
{{-- Zero-setup CDNs. For production, compile Tailwind/Alpine with Vite instead (Breeze already ships with it). --}}
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="{{ asset('js/bh-map-bridge.js') }}"></script>
@if($boarderThemePage)
<script defer src="{{ asset('js/theme-toggle.js') }}?v={{ filemtime(public_path('js/theme-toggle.js')) }}"></script>
@endif
@stack('styles')
</head>
<body class="h-full antialiased @unless($portalV2)text-slate-800 @endunless {{ $residentPage ? 'tenant-theme' : '' }}">
@yield('body')
{{-- Toast for flash messages --}}
@if(!request()->routeIs('tenant.*', 'profile.edit') && (session('status') || $errors->any()))
<div x-data="{o:true}" x-init="setTimeout(()=>o=false,4500)" x-show="o" x-transition x-cloak
     class="fixed left-1/2 top-3 z-[60] w-[92%] max-w-sm -translate-x-1/2 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg {{ $errors->any() ? 'border-amber-500 bg-[#3a2a08] text-[#fcd34d]' : 'border-emerald-800 bg-[#0a2e22] text-[#6ee7b7]' }}"
     @click="o=false">{{ session('status') ?? $errors->first() }}</div>
@endif
@stack('scripts')
</body>
</html>
