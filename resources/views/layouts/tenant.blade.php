@extends('layouts.base')
@section('body')
@php
  $u = auth()->user();
@endphp
@push('styles')
<meta name="color-scheme" content="dark light">
<meta name="view-transition" content="same-origin">
<style>html{background:#080808;color-scheme:dark}@media(prefers-color-scheme:light){html:not([data-theme]){background:#e4e9e8;color-scheme:light}}html[data-theme="light"]{background:#e4e9e8;color-scheme:light}</style>
<link rel="expect" href="#portal-main" blocking="render">
<link rel="stylesheet" href="{{ asset('css/portal-v2.css') }}?v={{ filemtime(public_path('css/portal-v2.css')) }}">
@endpush
<div class="portal-v2">
  <header class="bar">
    <a class="mk" href="{{ route('tenant.home') }}" aria-label="BoardersHub Boarder Dashboard home">
      <i aria-hidden="true"><svg class="i" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8M5 10v10h14V10"/></svg></i>
      BoardersHub
    </a>
    <nav class="nav" aria-label="Sections" data-portal-v2-nav>
      @foreach([
        ['tenant.home', 'Home', 'M3 11l9-8 9 8M5 10v10h14V10'],
        ['tenant.billing', 'Billing', 'M2 5h20v14H2zM2 10h20'],
        ['tenant.map', 'Room', 'M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14'],
        ['tenant.wifi-devices', 'Utilities', 'M13 2L5 14h6l-1 8 8-12h-6z'],
        ['tenant.maintenance', 'Repairs', 'M14 4l6 6L9 21H3v-6z'],
        ['profile.edit', 'Account', 'M12 4a4 4 0 100 8 4 4 0 000-8zM4 21c0-4 4-6 8-6s8 2 8 6'],
      ] as [$routeName, $label, $icon])
        <a class="nv" href="{{ route($routeName) }}" data-portal-v2-route="{{ $routeName }}" @if(request()->routeIs($routeName)) aria-current="page" @endif>
          <svg aria-hidden="true" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg><span>{{ $label }}</span>
        </a>
      @endforeach
    </nav>
    <div class="rt">
      <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
        <svg data-theme-icon="light" aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
        <svg data-theme-icon="dark" aria-hidden="true" viewBox="0 0 24 24"><path d="M20.5 15.5A8.5 8.5 0 018.5 3.5 8.5 8.5 0 1020.5 15.5z"/></svg>
        <span data-theme-label>Light</span>
      </button>
      <span class="cap">{{ $u?->name ?? 'Boarder' }}</span>
      <div class="av" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u?->name ?? 'R', 0, 1)) }}</div>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn o s" type="submit">Log out</button></form>
    </div>
  </header>
  <main id="portal-main">@yield('content')</main>
  <div class="toast" id="tt" role="status" aria-live="polite">@if(session('status') || $errors->any()){{ session('status') ?? $errors->first() }}@endif</div>
</div>
<script defer src="{{ asset('js/portal-v2.js') }}?v={{ filemtime(public_path('js/portal-v2.js')) }}"></script>
@endsection
