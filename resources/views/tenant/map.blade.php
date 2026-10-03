@extends('layouts.tenant')
@section('title','Map · BoardersHub')
@section('content')
<section class="space-y-3">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <div><h1 class="text-xl font-bold text-slate-900">Room map</h1><p class="text-sm text-slate-500">Explore the property and find your room.</p></div>
    @if($bed)<span class="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700">Your bed · Room {{ $bed->room->number }} · {{ $bed->label }}</span>@endif
  </div>

  @if(! $bed)
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-600">Your landlord will assign your room before the map is available.</div>
  @else
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
      <div class="relative h-[62vh] min-h-[420px] overflow-hidden rounded-xl bg-slate-100 md:h-[calc(100vh-13rem)] md:min-h-[600px]">
        <iframe id="bh-tenant-map" src="{{ asset('map/boarding_house_map.html') }}?embed=1&mode=tenant&theme=light&focus={{ urlencode($bed->mesh_id) }}&statusUrl={{ urlencode(route('map.statuses')) }}" class="h-full w-full border-0" title="Interactive room map" loading="lazy" allow="fullscreen" allowfullscreen></iframe>
      </div>
    </div>
  @endif
</section>
@endsection
