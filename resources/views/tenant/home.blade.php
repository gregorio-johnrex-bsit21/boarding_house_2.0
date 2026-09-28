@extends('layouts.tenant')
@section('title','Home · BoardersHub')
@section('content')
@if(! $bed)
  <div class="rounded-2xl bg-white p-6 text-center shadow-sm"><p class="text-3xl">🛏️</p><p class="mt-2 font-bold">No bed assigned yet</p>
  <p class="text-sm text-slate-500">Your landlord will assign you a bed. You'll see it here.</p></div>
@else
  <section class="rounded-2xl bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between">
      <div><p class="text-xs text-slate-500">Your bed</p><p class="text-xl font-bold">Room {{ $bed->room->number }} · {{ $bed->label }}</p></div>
      <x-badge :status="$billing['status']"/>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
      <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Monthly rate</p><p class="text-lg font-bold">₱{{ number_format($billing['amount']) }}</p></div>
      <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Next due</p><p class="text-lg font-bold">{{ $billing['due']->format('M j') }}</p></div>
    </div>
    @if(in_array($billing['status'],['unpaid','due_soon','overdue']))
      <a href="{{ route('tenant.billing') }}" class="mt-3 block rounded-xl bg-indigo-600 py-3 text-center text-sm font-semibold text-white">Pay now with GCash</a>
    @endif
    @if($roommates->isNotEmpty())<p class="mt-3 text-xs text-slate-500">Roommates: {{ $roommates->join(', ') }}</p>@endif
  </section>

  <section class="overflow-hidden rounded-2xl bg-white shadow-sm" x-data="{live:false}">
    <div class="flex items-center justify-between px-4 pt-3"><p class="font-bold">Where you stay</p><p class="text-xs text-slate-500">Pinch to zoom</p></div>
    <div class="relative m-3 h-72 overflow-hidden rounded-xl bg-slate-100">
      <iframe id="bh-map" src="{{ asset('map/boarding_house_map.html') }}?embed=1&mode=tenant&theme=light" class="h-full w-full border-0" title="3D map" loading="lazy"></iframe>
      {{-- The map captures touches, which would trap page scrolling: tap to interact first. --}}
      <button x-show="!live" @click="live=true" class="absolute inset-0 grid place-items-end bg-transparent p-3"><span class="rounded-full bg-black/60 px-3 py-1 text-xs text-white">Tap to explore the map</span></button>
    </div>
  </section>
@endif
@endsection
@push('scripts')
@if($bed)
<script>
new BHMap(document.getElementById('bh-map'), {
  onReady: m => { m.setStatuses(@json($statuses)); m.focusBed(@json($bed->mesh_id), '📍 You Are Here'); }
});
</script>
@endif
@endpush
