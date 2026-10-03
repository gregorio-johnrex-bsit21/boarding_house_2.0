<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'BoardersHub')</title>
{{-- Zero-setup CDNs. For production, compile Tailwind/Alpine with Vite instead (Breeze already ships with it). --}}
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="{{ asset('js/bh-map-bridge.js') }}"></script>
<style>
  [x-cloak]{display:none!important}
  body{background:#f3f5f8}
  .rounded-2xl.bg-white{border:1px solid #e1e6ed;box-shadow:0 3px 10px rgba(15,23,42,.045)}
</style>
</head>
<body class="h-full text-slate-800 antialiased">
@yield('body')
{{-- Toast for flash messages --}}
@if(session('status') || $errors->any())
<div x-data="{o:true}" x-init="setTimeout(()=>o=false,4500)" x-show="o" x-transition x-cloak
     class="fixed left-1/2 top-3 z-[60] w-[92%] max-w-sm -translate-x-1/2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg {{ $errors->any() ? 'bg-rose-600' : 'bg-emerald-600' }}"
     @click="o=false">{{ session('status') ?? $errors->first() }}</div>
@endif
@stack('scripts')
</body>
</html>
