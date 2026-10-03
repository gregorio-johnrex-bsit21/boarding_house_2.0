@extends('layouts.base')
@section('body')
@php $u = auth()->user(); $nav = [['tenant.home','Home','M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],['tenant.billing','Billing','M3 7h18v10H3zM3 11h18'],['tenant.map','Map','M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3zM9 3v15m6-12v15'],['tenant.maintenance','Repairs','M14 6l4 4-9 9H5v-4z'],['profile.edit','Account','M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0']]; @endphp
<div x-data="{report:false}" class="mx-auto min-h-screen max-w-md bg-slate-50 pb-28 md:max-w-none md:pb-8">
  <header class="sticky top-0 z-30 flex items-center justify-between bg-indigo-600 px-4 pb-4 pt-[max(1rem,env(safe-area-inset-top))] text-white md:border-b md:border-slate-200 md:bg-white md:px-8 md:py-4 md:text-slate-900">
    <div class="min-w-0"><p class="text-xs text-indigo-200 md:text-indigo-600">BoardersHub · Resident portal</p><p class="truncate text-lg font-bold leading-tight">Welcome, {{ $u->name }}</p></div>
    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
      @csrf
      <button class="flex items-center gap-1.5 rounded-lg border border-white/30 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10 md:border-slate-200 md:text-slate-700 md:hover:bg-slate-50">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Log out
      </button>
    </form>
  </header>

  <div class="mx-auto max-w-7xl md:px-8">
    <div class="hidden justify-end px-4 pb-3 pt-4 md:flex md:px-0">
      <nav class="flex w-fit gap-1 rounded-xl bg-slate-200/70 p-1" aria-label="Resident navigation">
        @foreach($nav as [$r,$l,$d])
          <a href="{{ route($r) }}" class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition {{ request()->routeIs($r) ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/></svg>{{ $l }}
          </a>
        @endforeach
      </nav>
    </div>
    <main class="min-w-0 space-y-4 p-4 md:py-4">@yield('content')</main>
  </div>

  {{-- Mobile page navigation --}}
  <nav class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] md:hidden">
    <div class="grid grid-cols-5 px-1 pt-1">
      @foreach($nav as [$r,$l,$d])
        <a href="{{ route($r) }}" class="flex min-w-0 flex-col items-center gap-0.5 py-2 text-[10px] {{ request()->routeIs($r) ? 'font-bold text-indigo-600' : 'text-slate-500' }}">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/></svg>{{ $l }}</a>
      @endforeach
    </div>
  </nav>

  {{-- Report Maintenance sheet --}}
  <div x-show="report" x-cloak class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" @click="report=false" x-transition.opacity></div>
    <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data"
          x-show="report" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
          class="absolute inset-x-0 bottom-0 mx-auto w-full max-w-md space-y-3 rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] md:inset-y-0 md:my-auto md:max-w-xl md:rounded-3xl md:pb-5">
      @csrf
      <div class="mx-auto h-1.5 w-10 rounded-full bg-slate-200"></div>
      <h2 class="text-lg font-bold">Report a problem</h2>
      <div class="grid grid-cols-2 gap-2">
        <select name="issue_category" required class="rounded-xl border-slate-300 text-sm">
          @foreach(\App\Models\MaintenanceRequest::CATEGORIES as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <select name="urgency" class="rounded-xl border-slate-300 text-sm"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="urgent">Urgent</option></select>
      </div>
      <textarea name="description" required maxlength="1000" rows="3" placeholder="What's wrong? Where exactly?" class="w-full rounded-xl border-slate-300 text-sm"></textarea>
      <label x-data="{n:''}" class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-slate-300 p-3 text-sm text-slate-600">
        <span class="text-xl">📷</span><span x-text="n || 'Take or choose a photo (optional)'"></span>
        <input type="file" name="image" accept="image/*" capture="environment" class="hidden" @change="n=$event.target.files[0]?.name||''">
      </label>
      <div class="flex gap-2"><button type="button" @click="report=false" class="flex-1 rounded-xl border border-slate-200 py-3 text-sm font-semibold">Cancel</button>
      <button class="flex-1 rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white">Send report</button></div>
    </form>
  </div>
</div>
@endsection
