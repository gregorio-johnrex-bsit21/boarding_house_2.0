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
      <iframe x-ref="map" src="{{ asset('map/boarding_house_map.html') }}?embed=1&mode=admin&theme=light" class="h-full w-full border-0 block" title="3D map" allow="fullscreen" allowfullscreen></iframe>
    </div>
  </section>

  {{-- ============ TENANTS ============ --}}
  <section x-show="tab==='tenants'" x-cloak class="space-y-4">
    {{-- Search Bar --}}
    <form method="GET" action="{{ route('admin.dashboard') }}#tenants" class="flex items-center gap-2">
      <input name="q" value="{{ $q }}" placeholder="Search name, room, email…" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
      @if($roomFilter)<input type="hidden" name="room" value="{{ $roomFilter }}">@endif
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

    {{-- Compact room dropdown and Add Tenant --}}
    <div class="flex w-full items-center justify-between gap-2">
      <form method="GET" action="{{ route('admin.dashboard') }}#tenants" class="min-w-0">
        <input type="hidden" name="q" value="{{ $q }}">
        <label for="tenant-room-filter" class="sr-only">Filter by room</label>
        <select id="tenant-room-filter" name="room" onchange="this.form.submit()" class="w-32 max-w-full rounded-xl border-slate-300 bg-white text-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-40">
          <option value="">All rooms</option>
          @foreach(range(1, 17) as $roomNumber)
            <option value="{{ $roomNumber }}" @selected((string) $roomFilter === (string) $roomNumber)>Room {{ $roomNumber }}</option>
          @endforeach
        </select>
      </form>
      <button @click="addTenantModal = true" class="shrink-0 flex items-center justify-center h-8 w-8 sm:h-auto sm:w-auto sm:px-3.5 sm:py-1.5 rounded-full bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 transition" title="Add Tenant">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline sm:ml-1.5 text-xs font-semibold">Add tenant</span>
      </button>
    </div>

    <p class="text-xs font-medium text-slate-500">{{ $tenants->count() }} {{ \Illuminate\Support\Str::plural('resident', $tenants->count()) }}{{ $roomFilter ? ' in Room '.$roomFilter : '' }}</p>

    {{-- Occupied tenant roster --}}
    <div class="space-y-3">
      @forelse($tenants as $x) @php $u=$x['user']; @endphp
        <div x-data="{detailOpen:false}" class="rounded-2xl bg-white p-4 shadow-sm">
          <div class="min-w-0">
            <p class="break-words font-semibold text-slate-900">{{ $u->name }}</p>
            <p class="break-words text-xs text-slate-500">{{ $u->bed?->full_name ?? 'No bed assigned' }}@if($u->phone) · {{ $u->phone }}@endif</p>
          </div>
          <div class="mt-3 flex justify-end border-t border-slate-100 pt-3">
            <button type="button" @click="detailOpen=true" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">View profile</button>
          </div>
          <x-detail-modal title="Resident profile">
            <div class="mb-5 flex items-center gap-4">
              <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-400" aria-label="No profile photo">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0A17.9 17.9 0 0112 22.5a17.9 17.9 0 01-7.5-2.4z"/></svg>
              </div>
              <div class="min-w-0"><h4 class="break-words text-lg font-bold text-slate-900">{{ $u->name }}</h4><p class="break-all text-sm text-slate-500">{{ $u->email }}</p></div>
            </div>
            <dl class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">
              <div><dt class="text-xs font-medium text-slate-500">Room</dt><dd class="mt-1 font-semibold text-slate-900">{{ $u->bed?->room?->number ?? '—' }}</dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Bed number</dt><dd class="mt-1 font-semibold text-slate-900">{{ $u->bed?->bed_no ? 'Bed '.$u->bed->bed_no : '—' }}</dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Bunk position</dt><dd class="mt-1 font-semibold text-slate-900">{{ $u->bed?->bunk === 'A' ? 'Top bunk' : ($u->bed?->bunk === 'B' ? 'Bottom bunk' : '—') }}</dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Phone</dt><dd class="mt-1 break-words font-semibold text-slate-900">{{ $u->phone ?: '—' }}</dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Move-in date</dt><dd class="mt-1 font-semibold text-slate-900">{{ $u->move_in_date?->format('M j, Y') ?? '—' }}</dd></div>
            </dl>
          </x-detail-modal>
        </div>
      @empty 
        <p class="p-8 text-center text-sm text-slate-500">No tenants match.</p> 
      @endforelse
    </div>
  </section>

  {{-- ============ PAYMENTS ============ --}}
  <section x-show="tab==='payments'" x-cloak class="space-y-4">
    <div class="flex min-w-0 gap-2 overflow-x-auto no-scrollbar pb-1">
      @foreach(['all' => 'All residents', 'paid' => 'Paid', 'pending' => 'Pending', 'due' => 'Due'] as $statusKey => $statusLabel)
        <a href="{{ route('admin.dashboard', $statusKey === 'all' ? [] : ['payment_status' => $statusKey]) }}#payments" class="shrink-0 whitespace-nowrap rounded-full px-3.5 py-2 text-xs font-semibold transition {{ $paymentFilter === $statusKey ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 shadow-sm hover:bg-slate-100' }}">{{ $statusLabel }}</a>
      @endforeach
    </div>
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
      @forelse($paymentResidents as $resident)
        @php
          $u = $resident['user']; $b = $resident['billing']; $last = $resident['latest_payment'];
          $badgeStatus = match($b['status']) { 'paid' => 'paid', 'pending' => 'pending', 'partial' => 'partial', default => 'due' };
          $cycleStart = $b['period']->copy()->startOfMonth();
          $cycleEnd = $cycleStart->copy()->addMonthNoOverflow();
        @endphp
        <article x-data="{paymentOpen:false,paymentMethod:'cash',paymentAmount:{{ (float) $b['remaining'] }},signing:false,signatureData:'',signatureContext:null,initSignature(){const c=this.$refs.signatureCanvas;if(!c)return;const r=c.getBoundingClientRect(),d=window.devicePixelRatio||1;c.width=r.width*d;c.height=r.height*d;this.signatureContext=c.getContext('2d');this.signatureContext.scale(d,d);this.signatureContext.lineWidth=2.5;this.signatureContext.lineCap='round';this.signatureContext.lineJoin='round';this.signatureContext.strokeStyle='#0f172a';this.signatureData='';},beginSignature(e){const c=this.$refs.signatureCanvas;if(!c||!this.signatureContext)return;e.preventDefault();c.setPointerCapture(e.pointerId);const r=c.getBoundingClientRect();this.signatureContext.beginPath();this.signatureContext.moveTo(e.clientX-r.left,e.clientY-r.top);this.signing=true;},drawSignature(e){if(!this.signing)return;const r=this.$refs.signatureCanvas.getBoundingClientRect();this.signatureContext.lineTo(e.clientX-r.left,e.clientY-r.top);this.signatureContext.stroke();this.signatureData=this.$refs.signatureCanvas.toDataURL('image/png');},endSignature(){this.signing=false;},clearSignature(){const c=this.$refs.signatureCanvas;if(c&&this.signatureContext)this.signatureContext.clearRect(0,0,c.width,c.height);this.signatureData='';}}" x-init="$watch('paymentOpen',open=>{if(open&&paymentMethod==='cash')$nextTick(()=>initSignature())})" class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><h3 class="break-words font-semibold text-slate-900">{{ $u->name }}</h3><p class="mt-1 break-words text-sm text-slate-500">{{ $u->bed?->full_name ?? 'No bed assigned' }}</p></div>
            <x-badge :status="$badgeStatus" />
          </div>
          <p class="mt-3 text-xs text-slate-500">Billing period · {{ $cycleStart->format('M j') }} – {{ $cycleEnd->format('M j, Y') }}</p>
          <div class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center">
            <div><p class="text-[11px] text-slate-500">Rent</p><p class="mt-1 text-sm font-semibold text-slate-900">₱{{ number_format($b['amount'], 2) }}</p></div>
            <div><p class="text-[11px] text-slate-500">Paid</p><p class="mt-1 text-sm font-semibold text-emerald-700">₱{{ number_format($b['paid_amount'], 2) }}</p></div>
            <div><p class="text-[11px] text-slate-500">Remaining</p><p class="mt-1 text-sm font-semibold text-slate-900">₱{{ number_format($b['remaining'], 2) }}</p></div>
          </div>
          <div class="mt-3 flex items-center justify-between gap-2 text-xs"><span class="text-slate-500">Due {{ $b['due']->format('M j, Y') }}</span>@if($last)<span class="text-slate-500">Latest: {{ ucwords(str_replace('_', ' ', $last->payment_method)) }}</span>@endif</div>
          @if($b['remaining'] > 0)
            <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
              <button type="button" @click="paymentMethod='cash';paymentAmount={{ (float) $b['remaining'] }};paymentOpen=true" class="rounded-xl bg-emerald-700 px-3 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Record cash</button>
              <button type="button" @click="paymentMethod='e_wallet';paymentAmount={{ (float) $b['remaining'] }};paymentOpen=true" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50">E-wallet / bank</button>
            </div>
          @endif
          <x-detail-modal title="Record payment" open="paymentOpen">
            <div class="space-y-4">
              <div class="rounded-xl bg-slate-50 p-3 text-sm"><p class="font-semibold text-slate-900">{{ $u->name }} · {{ $u->bed?->full_name ?? 'No bed assigned' }}</p><p class="mt-1 text-slate-600">Rent ₱{{ number_format($b['amount'], 2) }} · Paid ₱{{ number_format($b['paid_amount'], 2) }} · Remaining ₱{{ number_format($b['remaining'], 2) }}</p></div>
              <form method="POST" action="{{ route('admin.tenants.cash', $u) }}" class="space-y-3">
                @csrf<input type="hidden" name="payment_method" :value="paymentMethod">
                <label class="block text-sm font-medium text-slate-700">Amount received<div class="mt-1 flex items-center rounded-xl border border-slate-300 px-3 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-100"><span class="text-slate-500">₱</span><input name="amount" type="number" min="0.01" max="{{ number_format($b['remaining'], 2, '.', '') }}" step="0.01" x-model.number="paymentAmount" required class="w-full border-0 px-2 focus:ring-0"></div></label>
                <p class="text-xs text-slate-500">Balance after payment: <strong class="text-slate-800">₱<span x-text="Math.max(0, {{ (float) $b['remaining'] }} - (Number(paymentAmount) || 0)).toFixed(2)"></span></strong></p>
                <div x-show="paymentMethod==='cash'" x-cloak class="space-y-2">
                  <div><p class="text-sm font-semibold text-slate-800">Tenant signature</p><p class="text-xs text-slate-500">Ask the tenant to sign to confirm this cash payment.</p></div>
                  <canvas x-ref="signatureCanvas" @pointerdown="beginSignature($event)" @pointermove="drawSignature($event)" @pointerup="endSignature()" @pointercancel="endSignature()" class="h-32 w-full rounded-xl border border-dashed border-slate-300 bg-white" style="touch-action:none"></canvas>
                  <input type="hidden" name="tenant_signature" :value="signatureData">
                  <div class="flex items-center justify-between"><span class="text-xs text-slate-500" x-text="signatureData ? 'Signature captured' : 'Sign above' "></span><button type="button" @click="clearSignature()" class="text-xs font-semibold text-indigo-700 underline">Clear signature</button></div>
                </div>
                <label x-show="paymentMethod==='e_wallet'" x-cloak class="block text-sm font-medium text-slate-700">E-wallet / bank reference<input name="gcash_reference" type="text" maxlength="30" :required="paymentMethod==='e_wallet'" class="mt-1 w-full rounded-xl border-slate-300 text-sm" placeholder="Enter transaction reference"></label>
                <button :disabled="paymentMethod==='cash'&&!signatureData" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Save payment</button>
              </form>
            </div>
          </x-detail-modal>
        </article>
      @empty
        <p class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">{{ $paymentFilter === 'all' ? 'No residents to show.' : 'No residents match this payment status.' }}</p>
      @endforelse
    </div>
  </section>
  {{-- ============ MAINTENANCE ============ --}}
  <section x-show="tab==='maintenance'" x-cloak class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
    @forelse($requests as $m)
      <article x-data="{s:'{{ $m->status }}',busy:false,detailOpen:false,async set(n){this.busy=true;try{const r=await fetch(BH.base+'/admin/maintenance/{{ $m->id }}',{method:'PATCH',headers:BH.h,body:JSON.stringify({status:n})});if(r.ok)this.s=n}finally{this.busy=false}}}"
               @click="detailOpen=true" @keydown.enter="detailOpen=true" @keydown.space.prevent="detailOpen=true" role="button" tabindex="0" aria-label="View repair details"
               class="cursor-pointer space-y-2 rounded-2xl bg-white p-4 shadow-sm transition hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="font-semibold text-slate-900">{{ \App\Models\MaintenanceRequest::CATEGORIES[$m->issue_category] ?? $m->issue_category }}
            @if($m->urgency==='urgent')<span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-600">URGENT</span>@endif</p>
            <p class="text-xs text-slate-500">{{ $m->tenant->name }} · {{ $m->bed?->full_name ?? '—' }} · {{ $m->created_at->diffForHumans() }}</p>
          </div>
          <span x-show="s==='pending'" class="shrink-0 whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-semibold italic tracking-wide text-slate-900">Pending</span>
          <span x-show="s==='in_progress'" x-cloak class="shrink-0 whitespace-nowrap rounded-md bg-sky-700 px-2.5 py-1 text-xs font-semibold tracking-wide text-white shadow-sm">In progress</span>
          <span x-show="s==='resolved'" x-cloak class="rounded-md bg-emerald-700 px-2.5 py-1 text-xs font-semibold tracking-wide text-white shadow-sm">Resolved</span>
        </div>
        <p class="text-sm text-slate-600">{{ $m->description }}</p>
        @if($m->image)<a href="{{ asset('storage/'.$m->image) }}" target="_blank"><img src="{{ asset('storage/'.$m->image) }}" class="h-28 w-full rounded-xl object-cover" alt="Photo"></a>@endif
        <div class="flex gap-2 pt-1" :class="busy && 'opacity-50 pointer-events-none'" @click.stop>
          <button x-show="s==='pending'" @click="set('in_progress')" class="flex-1 rounded-lg bg-sky-600 py-2 text-xs font-semibold text-white hover:bg-sky-500">Start work</button>
          <button x-show="s==='in_progress'" x-cloak @click="set('resolved')" class="flex-1 rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Mark resolved</button>
          <button x-show="s==='resolved'" x-cloak @click="set('pending')" class="flex-1 rounded-lg border border-slate-200 py-2 text-xs font-semibold hover:bg-slate-50">Reopen</button>
        </div>
        <x-detail-modal :title="\App\Models\MaintenanceRequest::CATEGORIES[$m->issue_category] ?? $m->issue_category">
            <dl class="mt-4 grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">
              <div><dt class="text-xs font-medium text-slate-500">Urgency</dt><dd class="mt-1 font-semibold capitalize text-slate-900">{{ $m->urgency }}</dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Status</dt><dd class="mt-1 font-semibold capitalize text-slate-900" x-text="s.replace('_',' ')"></dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Tenant</dt><dd class="mt-1 break-words font-semibold text-slate-900">{{ $m->tenant->name }}</dd></div>
              <div><dt class="text-xs font-medium text-slate-500">Room / bed</dt><dd class="mt-1 font-semibold text-slate-900">{{ $m->bed?->full_name ?? '—' }}</dd></div>
              <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Reported</dt><dd class="mt-1 font-semibold text-slate-900">{{ $m->created_at->format('M j, Y · g:i A') }}</dd></div>
              <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Full description</dt><dd class="mt-1 whitespace-pre-wrap break-words text-sm leading-6 text-slate-700">{{ $m->description }}</dd></div>
              <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-slate-500">Images</dt>
                @if($m->image)
                  <a href="{{ asset('storage/'.$m->image) }}" target="_blank" class="mt-2 block"><img src="{{ asset('storage/'.$m->image) }}" class="max-h-64 w-full rounded-xl border border-slate-200 object-cover" alt="Repair photo"></a>
                @else
                  <div class="mt-2 flex min-h-24 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-400">No images attached</div>
                @endif
              </div>
            </dl>
        </x-detail-modal>
      </article>
    @empty 
      <div class="col-span-full rounded-2xl bg-white p-8 text-center text-sm text-slate-500">
        No repair requests found.
      </div> 
    @endforelse
  </section>

  {{-- ============ SETTINGS ============ --}}
  <section x-show="tab==='settings'" x-cloak>
    <form method="POST" action="{{ route('admin.settings') }}" class="grid min-w-0 gap-4 lg:grid-cols-2" x-data="{ surcharges: @js(old('device_surcharges', $settings['device_surcharges'])) }">@csrf
      <section class="min-w-0 space-y-4 rounded-2xl bg-white p-5 shadow-sm">
        <div><h2 class="font-bold text-slate-900">Rent</h2><p class="mt-1 text-xs text-slate-500">Default rates are used unless a bed has its own rate.</p></div>
        <label class="block text-sm font-medium text-slate-600">Default monthly rate per bed
          <input name="rent[default_monthly_rate]" type="number" min="0" step="0.01" required value="{{ old('rent.default_monthly_rate', $settings['rent']['default_monthly_rate']) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
        </label>
        <div class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2">
          <label class="block text-sm font-medium text-slate-600">Top bunk rate <span class="font-normal">(optional)</span>
            <input name="rent[top_bunk_rate]" type="number" min="0" step="0.01" value="{{ old('rent.top_bunk_rate', $settings['rent']['top_bunk_rate']) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
          </label>
          <label class="block text-sm font-medium text-slate-600">Bottom bunk rate <span class="font-normal">(optional)</span>
            <input name="rent[bottom_bunk_rate]" type="number" min="0" step="0.01" value="{{ old('rent.bottom_bunk_rate', $settings['rent']['bottom_bunk_rate']) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
          </label>
        </div>
        <label class="flex cursor-pointer items-center gap-3 text-sm text-slate-700">
          <input type="checkbox" name="rent[prorate]" value="1" {{ old('rent.prorate', $settings['rent']['prorate']) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
          <span>Prorate rent when a tenant moves in mid-month</span>
        </label>
      </section>

      <section class="min-w-0 space-y-4 rounded-2xl bg-white p-5 shadow-sm">
        <div><h2 class="font-bold text-slate-900">Due dates</h2><p class="mt-1 text-xs text-slate-500">These rules determine the tenant payment status.</p></div>
        <label class="block text-sm font-medium text-slate-600">Grace period before overdue
          <span class="mt-1 flex items-center gap-2"><input name="due_dates[grace_period_days]" type="number" min="0" max="31" required value="{{ old('due_dates.grace_period_days', $settings['due_dates']['grace_period_days']) }}" class="w-28 rounded-xl border-slate-300 text-sm"><span class="text-sm text-slate-500">days after due date</span></span>
        </label>
        <label class="block text-sm font-medium text-slate-600">Show “Due soon”
          <span class="mt-1 flex items-center gap-2"><input name="due_dates[due_soon_days]" type="number" min="0" max="31" required value="{{ old('due_dates.due_soon_days', $settings['due_dates']['due_soon_days']) }}" class="w-28 rounded-xl border-slate-300 text-sm"><span class="text-sm text-slate-500">days before the due date</span></span>
        </label>
      </section>

      <section class="min-w-0 space-y-4 rounded-2xl bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2"><div><h2 class="font-bold text-slate-900">Device surcharges</h2><p class="mt-1 text-xs text-slate-500">Set the monthly fee for each device type.</p></div>
          <button type="button" @click="surcharges.push({name:'',fee:''})" class="rounded-lg border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Add device</button>
        </div>
        <template x-for="(item, index) in surcharges" :key="index">
          <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_minmax(5rem,8rem)_auto] items-end gap-2">
            <label class="min-w-0 text-xs font-medium text-slate-500">Device type
              <input :name="`device_surcharges[${index}][name]`" x-model="item.name" maxlength="60" placeholder="e.g. Refrigerator" class="mt-1 w-full min-w-0 rounded-xl border-slate-300 text-sm">
            </label>
            <label class="min-w-0 text-xs font-medium text-slate-500">Monthly fee
              <input :name="`device_surcharges[${index}][fee]`" x-model="item.fee" type="number" min="0" step="0.01" class="mt-1 w-full min-w-0 rounded-xl border-slate-300 text-sm">
            </label>
            <button type="button" @click="surcharges.splice(index,1)" aria-label="Remove device surcharge" class="mb-0.5 rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Remove</button>
          </div>
        </template>
        <p x-show="surcharges.length===0" class="rounded-xl bg-slate-50 p-3 text-sm text-slate-500">No device surcharges added.</p>
      </section>

      <section class="min-w-0 space-y-4 rounded-2xl bg-white p-5 shadow-sm">
        <div><h2 class="font-bold text-slate-900">Utility splitting</h2><p class="mt-1 text-xs text-slate-500">Enter each monthly bill and how it should be split.</p></div>
        @foreach(['electricity' => 'Electricity', 'wifi' => 'Wi-Fi', 'water' => 'Water'] as $utilityKey => $utilityLabel)
          @php($utility = $settings['utilities'][$utilityKey] ?? ['total' => '0.00', 'split_method' => 'equal'])
          <div class="grid min-w-0 grid-cols-1 gap-3 border-t border-slate-100 pt-3 sm:grid-cols-2">
            <label class="block text-sm font-medium text-slate-600">{{ $utilityLabel }} monthly total
              <input name="utilities[{{ $utilityKey }}][total]" type="number" min="0" step="0.01" required value="{{ old("utilities.$utilityKey.total", $utility['total']) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
            </label>
            <label class="block text-sm font-medium text-slate-600">Split method
              <select name="utilities[{{ $utilityKey }}][split_method]" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                <option value="equal" {{ old("utilities.$utilityKey.split_method", $utility['split_method']) === 'equal' ? 'selected' : '' }}>Equal per occupant</option>
                <option value="devices" {{ old("utilities.$utilityKey.split_method", $utility['split_method']) === 'devices' ? 'selected' : '' }}>Weighted by devices</option>
              </select>
            </label>
          </div>
        @endforeach
      </section>

      <button class="rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 lg:col-span-2">Save Settings</button>
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
              <div class="flex gap-2">
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
