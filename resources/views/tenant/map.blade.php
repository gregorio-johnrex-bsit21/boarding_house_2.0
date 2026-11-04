@extends('layouts.tenant')
@section('title','Room · BoardersHub')
@section('portal-v2','enabled')
@section('content')
@php
  $beds = $bed?->room?->beds?->sortBy(fn ($item) => sprintf('%03d-%s', $item->bed_no, $item->bunk)) ?? collect();
  $occupiedBeds = $beds->filter(fn ($item) => $item->tenant !== null)->count();
@endphp
<div data-s>
  <p class="lbl">Room</p>
  <h1>Your room</h1>
  <p class="sub" style="margin-bottom:18px">Your room assignment, bed details, and property map.</p>
</div>

@if(! $bed)
  <div class="card em" data-s>
    <h2 class="ct">No room assigned yet</h2>
    <p class="cap" style="margin-top:8px">Your landlord will assign your room and bed before room details become available.</p>
  </div>
@else
  <div class="rmw">
    <div class="col">
      <section class="card mapc" data-s>
        <div class="hd">
          <div>
            <p class="lbl">Interactive property map</p>
            <h2 class="ct">Room {{ $bed->room->number }}</h2>
            <p class="cap">Your assigned bed is highlighted. Use the map controls to explore.</p>
          </div>
          <span class="bdg in">Your room</span>
        </div>
        <div class="map-frame">
          <iframe id="bh-tenant-map" src="{{ asset('map/boarding_house_map.html') }}?embed=1&amp;mode=tenant&amp;theme=dark&amp;focus={{ urlencode($bed->mesh_id) }}&amp;statusUrl={{ urlencode(route('map.statuses')) }}" title="Interactive property map showing your assigned room" loading="lazy" allow="fullscreen" allowfullscreen></iframe>
          <div class="map-loading" id="map-loading" role="status" aria-live="polite"><span class="spin" aria-hidden="true"></span>Loading map…</div>
        </div>
      </section>

      <section class="card" data-s>
        <div class="hd">
          <div><h2 class="ct">Beds in Room {{ $bed->room->number }}</h2><p class="cap">{{ $occupiedBeds }} of {{ $beds->count() }} beds occupied</p></div>
          <div class="lg" aria-label="Bed status legend">
            <span><u style="background:var(--lk)"></u>You</span>
            <span><u style="background:var(--bed-available)"></u>Available</span>
            <span><u style="background:var(--bed-occupied)"></u>Occupied</span>
            <span><u style="background:var(--bed-reserved)"></u>Reserved</span>
            <span><u style="background:var(--bed-ready)"></u>Ready</span>
          </div>
        </div>
        <div class="bks">
          @foreach($beds as $roomBed)
            @php
              $isMine = $roomBed->id === $bed->id;
              $isOccupied = $roomBed->tenant !== null;
              $bedState = $isMine ? 'You' : match($roomBed->status) {
                  'available' => $isOccupied ? 'Occupied' : 'Available',
                  'occupied' => 'Occupied',
                  'reserved' => 'Reserved',
                  'ready' => 'Ready',
                  default => $isOccupied ? 'Occupied' : ucfirst($roomBed->status),
              };
              $bedColor = match($bedState) {
                  'You' => 'var(--lk)',
                  'Available' => 'var(--bed-available)',
                  'Occupied' => 'var(--bed-occupied)',
                  'Reserved' => 'var(--bed-reserved)',
                  'Ready' => 'var(--bed-ready)',
                  default => 'var(--t2)',
              };
            @endphp
            <div class="bk">
              <svg viewBox="0 0 84 72" role="img" aria-label="Bed {{ $roomBed->bed_no }} {{ $roomBed->bunk === 'A' ? 'top' : 'bottom' }}: {{ $bedState }}">
                <rect x="6" y="6" width="5" height="62" rx="2" fill="var(--bd)"/>
                <rect x="73" y="6" width="5" height="62" rx="2" fill="var(--bd)"/>
                <rect x="6" y="16" width="72" height="12" rx="4" fill="{{ $roomBed->bunk === 'A' ? $bedColor : 'var(--sf2)' }}" stroke="{{ $roomBed->bunk === 'A' ? $bedColor : 'var(--bd)' }}" stroke-width="2"/>
                <rect x="6" y="46" width="72" height="12" rx="4" fill="{{ $roomBed->bunk === 'B' ? $bedColor : 'var(--sf2)' }}" stroke="{{ $roomBed->bunk === 'B' ? $bedColor : 'var(--bd)' }}" stroke-width="2"/>
              </svg>
              <strong>{{ $roomBed->label ?: 'Bed '.$roomBed->bed_no }}</strong>
              <span class="cap">{{ $bedState }}</span>
            </div>
          @endforeach
        </div>
      </section>
    </div>

    <aside class="col">
      <section class="card drw" data-s>
        <div class="hd" style="margin-bottom:6px">
          <div><p class="lbl">Your assignment</p><h2 class="ct" style="font-size:24px">Room {{ $bed->room->number }}</h2></div>
          <span class="bdg in">You</span>
        </div>
        <p class="cap">{{ $bed->label ?: 'Bed '.$bed->bed_no }} · {{ $bed->bunk === 'A' ? 'Top bunk' : ($bed->bunk === 'B' ? 'Bottom bunk' : 'Assigned bed') }}</p>
        <div class="li"><span>Monthly bed-space rent</span><b>₱{{ number_format($bed->rate, 2) }}</b></div>
        <div class="li"><span>Check-in date</span><b>{{ $user?->move_in_date?->format('M j, Y') ?? 'Not recorded' }}</b></div>
        <div class="li"><span>Room occupancy</span><b>{{ $occupiedBeds }} / {{ $beds->count() }}</b></div>
      </section>
    </aside>
  </div>
@endif
@endsection
