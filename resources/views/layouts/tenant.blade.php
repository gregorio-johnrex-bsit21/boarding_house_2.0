@extends('layouts.base')
@section('body')
@php $u = auth()->user(); $nav = [['tenant.home','Home','M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],['tenant.billing','Billing','M3 7h18v10H3zM3 11h18'],['tenant.maintenance','Repairs','M14 6l4 4-9 9H5v-4z'],['profile.edit','Account','M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0']]; @endphp
<div x-data="{report:false}" class="mx-auto min-h-full max-w-md bg-slate-50 pb-28">
  <header class="sticky top-0 z-30 flex items-center justify-between bg-indigo-600 px-4 pb-4 pt-[max(1rem,env(safe-area-inset-top))] text-white">
    <div><p class="text-xs text-indigo-200">Welcome back</p><p class="text-lg font-bold leading-tight">{{ $u->name }}</p></div>
    <span class="grid h-9 w-9 place-items-center rounded-full bg-white/15 text-lg">🏠</span>
  </header>
  <main class="space-y-4 p-4">@yield('content')</main>

  {{-- Bottom navigation with centered FAB --}}
  <nav class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)]">
    <div class="grid grid-cols-5 items-end px-2 pt-1">
      @foreach([0,1] as $i) @php [$r,$l,$d]=$nav[$i]; @endphp
        <a href="{{ route($r) }}" class="flex flex-col items-center gap-0.5 py-2 text-[11px] {{ request()->routeIs($r) ? 'font-bold text-indigo-600' : 'text-slate-500' }}">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/></svg>{{ $l }}</a>
      @endforeach
      <button @click="report=true" aria-label="Report maintenance" class="-mt-6 mx-auto grid h-14 w-14 place-items-center rounded-full bg-indigo-600 text-2xl text-white shadow-lg ring-4 ring-white active:scale-95">🛠️</button>
      @foreach([2,3] as $i) @php [$r,$l,$d]=$nav[$i]; @endphp
        <a href="{{ route($r) }}" class="flex flex-col items-center gap-0.5 py-2 text-[11px] {{ request()->routeIs($r) ? 'font-bold text-indigo-600' : 'text-slate-500' }}">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/></svg>{{ $l }}</a>
      @endforeach
    </div>
  </nav>

  {{-- Report Maintenance sheet --}}
  <div x-show="report" x-cloak class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" @click="report=false" x-transition.opacity></div>
    <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data"
          x-show="report" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
          class="absolute inset-x-0 bottom-0 mx-auto max-w-md space-y-3 rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
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
