@extends('layouts.admin')
@section('title','Dashboard · BoardersHub')
@section('content')
@php
  $cards = [
    ['Total beds', $stats['total'], 'text-slate-800', 'M3 10h18M3 14h18M5 6v12M19 6v12M5 10v4m14-4v4'],
    ['Occupied', $stats['occupied'], 'text-rose-600', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
    ['Available', $stats['available'], 'text-emerald-600', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['Pending payments', $stats['pendingPayments'], 'text-amber-600', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
    ['Open repairs', $stats['openIssues'], 'text-sky-600', 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z']
  ];

  $tabs = [
    'tenants' => ['Tenants', 0, 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    'payments' => ['Payments', $stats['pendingPayments'], 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
    'map' => ['Map', 0, 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.818V8.042a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
    'maintenance' => ['Repairs', $stats['openIssues'], 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z'],
    'settings' => ['Settings', 0, 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z']
  ];

  $stColor = ['available'=>'#30a46c', 'occupied'=>'#e5484d', 'reserved'=>'#3e8ef7', 'ready'=>'#e5a800'];
@endphp

<div x-data="adminApp()" class="pb-16 md:pb-0">

  {{-- Top Navigation Bar (Desktop) --}}
  <div class="hidden justify-end pb-3 md:flex">
    <div class="flex w-fit gap-1 rounded-xl bg-slate-200/70 p-1">
      @foreach($tabs as $k=>[$l,$n,$svg])
        <button @click="go('{{ $k }}')" :class="tab==='{{ $k }}' ? 'bg-white shadow-sm text-indigo-700' : 'text-slate-600 hover:text-slate-900'" class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $svg }}"/></svg>
          {{ $l }} 
          @if($n)<span class="rounded-full bg-rose-500 px-1.5 py-0.2 text-[10px] text-white font-bold">{{ $n }}</span>@endif
        </button>
      @endforeach
    </div>
  </div>

  {{-- ============ MAP (Full-width edge-to-edge canvas) ============ --}}
  <section x-show="tab==='map'" class="-mx-4 -mt-4 sm:mx-0 sm:mt-0">
    <div class="h-[calc(100vh-8.5rem)] md:h-[calc(100vh-6rem)] w-full overflow-hidden rounded-none sm:rounded-2xl bg-slate-100 touch-none">
      <iframe x-ref="map" src="{{ asset('map/boarding_house_map.html') }}?embed=1&mode=admin&theme=light" class="h-full w-full border-0 block" title="3D map"></iframe>
    </div>
  </section>

  {{-- ============ TENANTS ============ --}}
  <section x-show="tab==='tenants'" x-cloak class="space-y-4">
    {{-- Search Bar --}}
    <form method="GET" action="{{ route('admin.dashboard') }}#tenants" class="flex items-center gap-2">
      <input name="q" value="{{ $q }}" placeholder="Search name, room, email…" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
      @if($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
      <button class="flex items-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <span>Search</span>
      </button>
    </form>

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
      @foreach($cards as [$l,$v,$c,$svg])
        <div class="rounded-2xl bg-white p-4 shadow-sm {{ $loop->first ? 'col-span-2 md:col-span-1' : '' }}">
          <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-medium">{{ $l }}</span>
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $svg }}"/></svg>
          </div>
          <p class="mt-2 text-3xl font-extrabold {{ $c }}">{{ $v }}</p>
        </div>
      @endforeach
    </div>

    {{-- Filters & Compact Add Tenant Button --}}
    <div class="flex w-full items-center justify-between gap-1.5">
      <div class="flex items-center gap-1 sm:gap-1.5 min-w-0 flex-1 overflow-x-auto no-scrollbar">
        @foreach([''=>'All','due_soon'=>'Due soon','paid'=>'Paid','overdue'=>'Overdue'] as $f=>$l)
          <a href="{{ route('admin.dashboard',array_filter(['filter'=>$f,'q'=>$q])) }}#tenants" class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold transition shrink-0 {{ ($filter ?? '')===$f ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 shadow-sm' }}">{{ $l }}</a>
        @endforeach
      </div>

      {{-- Compact '+' Button on Mobile, Full Label on Desktop --}}
      <button @click="addTenantModal = true" class="shrink-0 flex items-center justify-center h-8 w-8 sm:h-auto sm:w-auto sm:px-3.5 sm:py-1.5 rounded-full bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 transition" title="Add Tenant">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline sm:ml-1.5 text-xs font-semibold">Add tenant</span>
      </button>
    </div>

    {{-- Tenant list --}}
    <div class="divide-y divide-slate-100 rounded-2xl bg-white shadow-sm">
      @forelse($tenants as $x) @php $u=$x['user']; $b=$x['billing']; @endphp
        <div class="flex flex-wrap items-center gap-3 p-4 hover:bg-slate-50/50 transition">
          <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-slate-900">{{ $u->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ $u->bed?->full_name ?? 'No bed assigned' }}@if($u->phone) · {{ $u->phone }}@endif</p>
          </div>
          <div class="text-right text-xs text-slate-500">Due {{ $b['due']->format('M j') }}<br><b class="text-sm text-slate-800">₱{{ number_format($b['amount']) }}</b></div>
          <x-badge :status="$b['status']"/>
          @if($u->bed && $b['status']!=='paid')
            <form method="POST" action="{{ route('admin.tenants.cash',$u) }}" onsubmit="return confirm('Mark ₱{{ number_format($b['amount']) }} cash received from {{ addslashes($u->name) }}?')">@csrf
              <button class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Cash paid
              </button>
            </form>
          @endif
        </div>
      @empty 
        <p class="p-8 text-center text-sm text-slate-500">No tenants match.</p> 
      @endforelse
    </div>
  </section>

  {{-- ============ PAYMENTS ============ --}}
  <section x-show="tab==='payments'" x-cloak class="space-y-3">
    @forelse($payments as $p)
      <article class="flex flex-wrap items-center gap-3 rounded-2xl bg-white p-4 shadow-sm">
        @if($p->receipt_image)
          <a href="{{ asset('storage/'.$p->receipt_image) }}" target="_blank"><img src="{{ asset('storage/'.$p->receipt_image) }}" class="h-16 w-16 rounded-lg object-cover border" alt="Receipt"></a>
        @endif
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900">{{ $p->user->name }} · ₱{{ number_format($p->amount) }}</p>
          <p class="text-xs text-slate-500">{{ $p->user->bed?->full_name }} · {{ $p->period->format('F Y') }} · {{ strtoupper($p->payment_method) }}@if($p->gcash_reference) · Ref {{ $p->gcash_reference }}@endif</p>
        </div>
        <x-badge :status="$p->status"/>
        @if($p->status==='pending')
          <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.payments.reject',$p) }}">@csrf<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Reject</button></form>
            <form method="POST" action="{{ route('admin.payments.verify',$p) }}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Verify</button></form>
          </div>
        @endif
      </article>
    @empty 
      <p class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500">No payments yet.</p> 
    @endforelse
  </section>

  {{-- ============ MAINTENANCE ============ --}}
  <section x-show="tab==='maintenance'" x-cloak class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
    @forelse($requests as $m)
      <article x-data="{s:'{{ $m->status }}',busy:false,async set(n){this.busy=true;try{const r=await fetch(BH.base+'/admin/maintenance/{{ $m->id }}',{method:'PATCH',headers:BH.h,body:JSON.stringify({status:n})});if(r.ok)this.s=n}finally{this.busy=false}}}"
               class="space-y-2 rounded-2xl bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="font-semibold text-slate-900">{{ \App\Models\MaintenanceRequest::CATEGORIES[$m->issue_category] ?? $m->issue_category }}
            @if($m->urgency==='urgent')<span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-600">URGENT</span>@endif</p>
            <p class="text-xs text-slate-500">{{ $m->tenant->name }} · {{ $m->bed?->full_name ?? '—' }} · {{ $m->created_at->diffForHumans() }}</p>
          </div>
          <span x-show="s==='pending'" class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
          <span x-show="s==='in_progress'" x-cloak class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">In progress</span>
          <span x-show="s==='resolved'" x-cloak class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Resolved</span>
        </div>
        <p class="text-sm text-slate-600">{{ $m->description }}</p>
        @if($m->image)<a href="{{ asset('storage/'.$m->image) }}" target="_blank"><img src="{{ asset('storage/'.$m->image) }}" class="h-28 w-full rounded-xl object-cover" alt="Photo"></a>@endif
        <div class="flex gap-2 pt-1" :class="busy && 'opacity-50 pointer-events-none'">
          <button x-show="s==='pending'" @click="set('in_progress')" class="flex-1 rounded-lg bg-sky-600 py-2 text-xs font-semibold text-white hover:bg-sky-500">Start work</button>
          <button x-show="s==='in_progress'" x-cloak @click="set('resolved')" class="flex-1 rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Mark resolved</button>
          <button x-show="s==='resolved'" x-cloak @click="set('pending')" class="flex-1 rounded-lg border border-slate-200 py-2 text-xs font-semibold hover:bg-slate-50">Reopen</button>
        </div>
      </article>
    @empty 
      <div class="col-span-full rounded-2xl bg-white p-8 text-center text-sm text-slate-500">
        No repair requests found.
      </div> 
    @endforelse
  </section>

  {{-- ============ SETTINGS ============ --}}
  <section x-show="tab==='settings'" x-cloak>
    <form method="POST" action="{{ route('admin.settings') }}" class="grid gap-4 lg:grid-cols-2">@csrf
      
      <div class="space-y-3 rounded-2xl bg-white p-5 shadow-sm">
        <div class="border-b border-slate-100 pb-2">
          <p class="font-bold text-slate-900">General Information</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Boarding House Name</label>
          <input name="property_name" value="{{ old('property_name', $settings['property_name'] ?? 'BoardersHub') }}" class="w-full rounded-xl border-slate-300 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Phone</label>
            <input name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" placeholder="+63 900 000 0000" class="w-full rounded-xl border-slate-300 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Support Email</label>
            <input name="contact_email" type="email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" placeholder="admin@example.com" class="w-full rounded-xl border-slate-300 text-sm">
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Currency Symbol</label>
          <input name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₱') }}" class="w-24 rounded-xl border-slate-300 text-sm">
        </div>
      </div>

      <div class="space-y-3 rounded-2xl bg-white p-5 shadow-sm">
        <div class="border-b border-slate-100 pb-2">
          <p class="font-bold text-slate-900">System Preferences</p>
        </div>
        <label class="flex items-center gap-3 cursor-pointer py-1">
          <input type="checkbox" name="auto_remind_overdue" value="1" {{ old('auto_remind_overdue', $settings['auto_remind_overdue'] ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
          <span class="text-xs font-medium text-slate-700">Send automatic payment reminders for overdue billing</span>
        </label>
        <label class="flex items-center gap-3 cursor-pointer py-1">
          <input type="checkbox" name="notify_maintenance" value="1" {{ old('notify_maintenance', $settings['notify_maintenance'] ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
          <span class="text-xs font-medium text-slate-700">Send email notification when new repair request is filed</span>
        </label>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm lg:col-span-2">
        <p class="mb-3 font-bold text-slate-900">Monthly Rates per Room Bunk</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          @foreach($rooms as $room)
            <label class="text-xs font-medium text-slate-500">Room {{ $room->number }}
              <input name="rates[{{ $room->id }}]" type="number" min="0" step="0.01" value="{{ $room->monthly_rate }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-800">
            </label>
          @endforeach
        </div>
      </div>

      <button class="rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white hover:bg-indigo-500 lg:col-span-2 transition">Save Settings</button>
    </form>
  </section>

  {{-- ============ ADD TENANT MODAL ============ --}}
  <div x-show="addTenantModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addTenantModal = false" x-transition.opacity></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <h3 class="text-lg font-bold text-slate-900">Add Tenant Account</h3>
        <button @click="addTenantModal = false" class="rounded-full bg-slate-100 p-1 text-slate-500 hover:bg-slate-200">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form method="POST" action="{{ route('admin.tenants.store') }}" class="mt-4 space-y-3">@csrf
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
          <input name="name" required placeholder="John Doe" class="w-full rounded-xl border-slate-300 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
          <input name="email" type="email" required placeholder="john@example.com" class="w-full rounded-xl border-slate-300 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
          <input name="phone" placeholder="09123456789" class="w-full rounded-xl border-slate-300 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Temporary Password</label>
          <input name="password" type="password" required minlength="8" placeholder="••••••••" class="w-full rounded-xl border-slate-300 text-sm">
        </div>
        <div class="pt-2 flex justify-end gap-2">
          <button type="button" @click="addTenantModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
          <button class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Tenant</button>
        </div>
      </form>
    </div>
  </div>

  {{-- FIXED MOBILE BOTTOM NAVIGATION BAR (Solid background, no blur) --}}
  <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white md:hidden" style="padding-bottom:env(safe-area-inset-bottom)">
    <div class="grid grid-cols-5 h-14">
      @foreach($tabs as $k=>[$l,$n,$svg])
        <button @click="go('{{ $k }}')" :class="tab==='{{ $k }}' ? 'text-indigo-600 font-bold' : 'text-slate-500 font-medium'" class="relative flex flex-col items-center justify-center gap-0.5 text-[10px]">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $svg }}"/></svg>
          <span>{{ $l }}</span>
          @if($n)<span class="absolute top-1 right-3 rounded-full bg-rose-500 px-1 text-[9px] leading-3 text-white font-bold">{{ $n }}</span>@endif
        </button>
      @endforeach
    </div>
  </nav>

  {{-- ============ BED SLIDE-OVER ============ --}}
  <div x-show="sel" x-cloak class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/30" @click="close()" x-transition.opacity></div>
    <aside x-show="sel" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full" x-transition:enter-end="translate-y-0 md:translate-x-0"
           class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-3xl bg-white p-5 pb-8 shadow-2xl md:inset-y-0 md:left-auto md:right-0 md:max-h-none md:w-[26rem] md:rounded-none">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs text-slate-500">Room <span x-text="sel?.room"></span></p>
          <h2 class="text-xl font-bold" x-text="sel?.title"></h2>
        </div>
        <button @click="close()" class="rounded-full bg-slate-100 px-3 py-1 text-sm">✕</button>
      </div>
      <template x-for="b in (sel?.bunks||[])" :key="b.id">
        <div class="mt-4 space-y-3 rounded-2xl border border-slate-200 p-4">
          <div class="flex items-center justify-between">
            <p class="font-bold" x-text="b.label.split('· ')[1] + ' bunk'"></p>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold text-white" :style="'background:'+BH.colors[b.status]" x-text="BH.names[b.status]"></span>
          </div>
          <div class="grid grid-cols-2 gap-1.5">
            <template x-for="(n,k) in BH.names" :key="k">
              <button @click="setStatus(b,k)" class="rounded-lg border-2 px-2 py-1.5 text-xs font-semibold" :style="b.status===k ? 'border-color:'+BH.colors[k]+';background:'+BH.colors[k]+'22' : 'border-color:transparent;background:#f1f5f9'" x-text="n"></button>
            </template>
          </div>
          <template x-if="b.tenant">
            <div class="space-y-2 rounded-xl bg-slate-50 p-3 text-sm">
              <p class="font-semibold" x-text="b.tenant.name"></p>
              <p class="text-xs text-slate-500"><span x-text="b.tenant.phone || 'No phone'"></span> · due <span x-text="b.tenant.due"></span> · <b x-text="b.tenant.billing.replace('_',' ')"></b></p>
              <div class="flex gap-2">
                <form method="POST" :action="BH.base+'/admin/tenants/'+b.tenant.id+'/cash'" class="flex-1" onsubmit="return confirm('Mark cash payment received?')"><input type="hidden" name="_token" :value="BH.csrf"><button class="w-full rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white">Cash paid</button></form>
                <form method="POST" :action="BH.base+'/admin/beds/'+b.id+'/vacate'" class="flex-1" onsubmit="return confirm('Move this tenant out?')"><input type="hidden" name="_token" :value="BH.csrf"><button class="w-full rounded-lg border border-rose-200 py-2 text-xs font-semibold text-rose-600">Move out</button></form>
              </div>
            </div>
          </template>
          <template x-if="!b.tenant && BH.unassigned.length">
            <form method="POST" :action="BH.base+'/admin/beds/'+b.id+'/assign'" class="flex gap-2"><input type="hidden" name="_token" :value="BH.csrf">
              <select name="user_id" required class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm"><option value="">Assign tenant…</option>
                <template x-for="u in BH.unassigned" :key="u.id"><option :value="u.id" x-text="u.name"></option></template></select>
              <button class="rounded-lg bg-indigo-600 px-3 text-xs font-semibold text-white">Assign</button></form>
          </template>
        </div>
      </template>
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script>
window.BH = {
  base: @json(url('/')), csrf: document.querySelector('meta[name=csrf-token]').content,
  beds: @json($bedData), statuses: @json($statuses), unassigned: @json($unassigned), tab: @json(session('tab')),
  names: {available:'Available', occupied:'Occupied', reserved:'Reserved', ready:'Ready to be available'},
  colors: @json($stColor),
};
BH.h = {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':BH.csrf};

function adminApp() {
  return {
    tab: (t => ['tenants','payments','map','maintenance','settings'].includes(t) ? t : 'tenants')(BH.tab || location.hash.slice(1)),
    beds: BH.beds, sel: null, map: null, addTenantModal: false,
    init() {
      this.$watch('tab', t => {
        history.replaceState(null, '', '#' + t);
        if (t === 'map') this.$nextTick(() => {
          const w = this.$refs.map.contentWindow;
          [0, 100, 300].forEach(ms => setTimeout(() => w && w.dispatchEvent(new Event('resize')), ms));
        });
      });
      this.map = new BHMap(this.$refs.map, {
        onReady: m => m.setStatuses(BH.statuses),
        onSelect: d => d.level === 'bed' ? this.open(d.meshIds) : (this.sel = null),
      });
    },
    go(k) { this.tab = k; window.scrollTo({ top: 0 }); },
    open(ids) {
      const bunks = ids.map(i => this.beds[i]).filter(Boolean);
      if (bunks.length) this.sel = { room: bunks[0].room, title: bunks[0].label.split(' · ')[0], bunks };
    },
    close() { this.sel = null; },
    async setStatus(b, status) {
      const r = await fetch(BH.base + '/admin/beds/' + b.id + '/status', {method:'PATCH', headers: BH.h, body: JSON.stringify({status})});
      if (r.ok) { this.beds[b.mesh_id].status = status; this.map.setStatus(b.mesh_id, status); }
    },
  };
}
</script>
@endpush