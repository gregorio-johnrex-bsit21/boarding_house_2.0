@extends('layouts.tenant')
@section('title','Utilities & devices · BoardersHub')
@section('portal-v2','enabled')
@section('content')
@php
  $pendingDeclarations = $deviceDeclarations->where('status', 'pending');
  $approvedDeclarations = $deviceDeclarations->where('status', 'approved');
  $deviceLines = collect($billing['lines'])->where('type', 'device');
  $monthlyAddedCents = \App\Support\Billing::amountToCents($billing['device_total']);
@endphp

<div data-s>
  <p class="lbl">Utilities</p>
  <h1>Utilities &amp; devices</h1>
  <p class="sub" style="margin-bottom:18px">Review your declared devices for {{ $billing['period']->format('F Y') }}.</p>
</div>

<div class="ut">
  <p class="card cap" data-s style="grid-column:1/-1">Electricity and water are included in your rent.</p>

  <section class="card" data-s>
    <div class="hd">
      <div><h2 class="ct">My devices</h2><p class="cap">Your landlord reviews each declaration and sets any monthly fee.</p></div>
      <button class="btn s" type="button" data-v2-open data-v2-title="Declare a device">Declare a device</button>
    </div>
    <div class="device-tiles">
      @foreach($deviceCatalog as $catalogDevice)
      @php
        $catalogUnitsInUse = $catalogDevice->unitsInUse();
        $catalogUnitsLeft = $catalogDevice->unitsLeft();
        $catalogCanDeclare = $catalogDevice->available && ($catalogUnitsLeft === null || $catalogUnitsLeft > 0);
      @endphp
      @if($catalogCanDeclare)
        <button class="device-tile" type="button" data-v2-open data-v2-title="Declare a device" data-catalog-device-choice="{{ $catalogDevice->id }}">
          <span class="device-tile-bar device-tile-bar-ok" aria-hidden="true"></span>
          <span class="device-tile-content">
            <strong>{{ $catalogDevice->name }}</strong>
            @if($catalogDevice->note)<span class="cap">{{ $catalogDevice->note }}</span>@endif
            @if($catalogDevice->total_units === null)
              <span class="device-available">● Available</span>
            @elseif($catalogUnitsLeft > 0)
              <span class="unit-count-label"><span class="device-available">● {{ $catalogUnitsLeft }} available</span> · <span class="device-unavailable">✕ {{ $catalogUnitsInUse }} in use</span></span>
              <span class="unit-dots" aria-hidden="true">
                @for($unit = 0; $unit < $catalogDevice->total_units; $unit++)
                  <i class="unit-dot {{ $unit < $catalogUnitsLeft ? 'unit-dot-available' : 'unit-dot-in-use' }}"></i>
                @endfor
              </span>
            @endif
          </span>
        </button>
      @else
        <div class="device-tile device-tile-unavailable" aria-disabled="true">
          <span class="device-tile-bar device-tile-bar-er" aria-hidden="true"></span>
          <span class="device-tile-content">
            <strong>{{ $catalogDevice->name }}</strong>
            @if($catalogDevice->note)<span class="cap">{{ $catalogDevice->note }}</span>@endif
            @if($catalogDevice->total_units === null)
              <span class="device-unavailable">✕ Not available</span>
            @else
              <span class="device-unavailable">✕ None available ({{ $catalogUnitsInUse }} of {{ $catalogDevice->total_units }} in use)</span>
              <span class="unit-dots" aria-hidden="true">
                @for($unit = 0; $unit < $catalogDevice->total_units; $unit++)
                  <i class="unit-dot {{ $unit < $catalogUnitsInUse ? 'unit-dot-in-use' : 'unit-dot-unavailable' }}"></i>
                @endfor
              </span>
            @endif
            @if($catalogDevice->reason)<span class="device-unavailable">{{ $catalogDevice->reason }}</span>@endif
          </span>
        </div>
        @endif
      @endforeach
      <button class="device-tile" type="button" data-v2-open data-v2-title="Declare a device" data-catalog-other>
        <span class="device-tile-bar device-tile-bar-neutral" aria-hidden="true"></span>
        <span class="device-tile-content">
          <strong>Other device</strong>
          <span class="cap">Enter a device name or model</span>
        </span>
      </button>
    </div>
    @forelse($deviceDeclarations as $declaration)
      @php
        $statusTone = match($declaration->status) { 'approved' => 'ok', 'rejected' => 'er', default => 'wn' };
        $statusLabel = match($declaration->status) { 'approved' => 'Approved', 'rejected' => 'Rejected', default => 'Pending review' };
      @endphp
      <div class="li" style="align-items:flex-start">
        <div>
          <b>{{ $declaration->name }}</b>
          @if($declaration->note)<p class="cap">{{ $declaration->note }}</p>@endif
          @if($declaration->status === 'approved')
            <p class="cap">Monthly fee · ₱{{ number_format($declaration->monthly_fee ?? 0, 2) }}</p>
          @elseif($declaration->status === 'rejected')
            <p class="cap" style="color:var(--er)">Reason: {{ $declaration->rejection_reason }}</p>
          @endif
          <p class="cap">{{ $declaration->created_at->format('M j, Y') }}</p>
          @if($declaration->status === 'pending')
            <form method="POST" action="{{ route('tenant.device-declarations.destroy', $declaration) }}" style="margin-top:8px">
              @csrf
              @method('DELETE')
              <button class="btn g s" type="submit">Cancel request</button>
            </form>
          @endif
        </div>
        <span class="bdg {{ $statusTone }}">{{ $statusLabel }}</span>
      </div>
    @empty
      <div class="em">No devices declared yet. Declare a device and your landlord will review it.</div>
    @endforelse
  </section>

  <section class="card" data-s>
    <h2 class="ct">Monthly effect on your bill</h2>
    @forelse($deviceLines as $deviceLine)
      <div class="li"><span>{{ $deviceLine['label'] }}</span><b>₱{{ number_format($deviceLine['amount'], 2) }}</b></div>
    @empty
      <p class="cap" style="margin-top:8px">No approved devices are being charged this period.</p>
    @endforelse
    <div class="li"><strong>Total added</strong><strong>₱{{ number_format(\App\Support\Billing::centsToAmount($monthlyAddedCents), 2) }}</strong></div>
    <div class="li"><span>Remaining balance</span><b>₱{{ number_format($billing['remaining'], 2) }}</b></div>
    <a class="btn g s" href="{{ route('tenant.billing') }}" style="margin-top:8px">View billing →</a>
  </section>

  <section class="card" data-s style="grid-column:1/-1">
    <h2 class="ct" style="margin-bottom:6px">What these charges pay for</h2>
    <div class="li" style="align-items:flex-start">
      <b style="min-width:170px">Approved devices</b>
      <span class="cap" style="flex:1 1 260px">
        @if($deviceLines->isNotEmpty())
          {{ $deviceLines->count() }} approved device charge(s) are included in this period.
        @else
          No devices have been approved yet.
        @endif
      </span>
    </div>
  </section>
</div>

<div class="ov" id="ovl" @if(!$errors->any()) hidden @endif>
  <section class="shn" role="dialog" aria-modal="true" aria-labelledby="st">
    <div class="hd">
      <div><p class="lbl">DEVICES</p><h2 class="ct" id="st">Declare a device</h2></div>
      <button class="btn o s" type="button" data-v2-close>Close</button>
    </div>
    <form method="POST" action="{{ route('tenant.device-declarations.store') }}" class="f" data-v2-validate>
      @csrf
      <fieldset class="device-choice-list" data-device-choice-list>
        <legend class="lbl">Choose a device</legend>
        @foreach($deviceCatalog as $catalogDevice)
          @php
            $catalogUnitsInUse = $catalogDevice->unitsInUse();
            $catalogUnitsLeft = $catalogDevice->unitsLeft();
            $catalogCanDeclare = $catalogDevice->available && ($catalogUnitsLeft === null || $catalogUnitsLeft > 0);
          @endphp
          <label class="device-choice {{ $catalogCanDeclare ? '' : 'device-choice-unavailable' }}" @if(!$catalogCanDeclare) aria-disabled="true" @endif>
            <span class="device-tile-bar {{ $catalogCanDeclare ? 'device-tile-bar-ok' : 'device-tile-bar-er' }}" aria-hidden="true"></span>
            <input type="radio" name="catalog_device_id" value="{{ $catalogDevice->id }}"
              @checked((string) old('catalog_device_id') === (string) $catalogDevice->id)
              @disabled(! $catalogCanDeclare)>
            <span class="device-choice-content">
              <strong>{{ $catalogDevice->name }}</strong>
              @if($catalogDevice->note)<span class="cap">{{ $catalogDevice->note }}</span>@endif
              @if($catalogDevice->total_units === null && $catalogDevice->available)
                <span class="device-available">● Available</span>
              @elseif($catalogDevice->total_units !== null && $catalogUnitsLeft > 0)
                <span class="unit-count-label"><span class="device-available">● {{ $catalogUnitsLeft }} available</span> · <span class="device-unavailable">✕ {{ $catalogUnitsInUse }} in use</span></span>
                <span class="unit-dots" aria-hidden="true">
                  @for($unit = 0; $unit < $catalogDevice->total_units; $unit++)
                    <i class="unit-dot {{ $unit < $catalogUnitsLeft ? 'unit-dot-available' : 'unit-dot-in-use' }}"></i>
                  @endfor
                </span>
              @elseif($catalogDevice->total_units !== null)
                <span class="device-unavailable">✕ None available ({{ $catalogUnitsInUse }} of {{ $catalogDevice->total_units }} in use)</span>
                <span class="unit-dots" aria-hidden="true">
                  @for($unit = 0; $unit < $catalogDevice->total_units; $unit++)
                    <i class="unit-dot {{ $unit < $catalogUnitsInUse ? 'unit-dot-in-use' : 'unit-dot-unavailable' }}"></i>
                  @endfor
                </span>
              @else
                <span class="device-unavailable">✕ Not available</span>
              @endif
              @if(!$catalogDevice->available && $catalogDevice->reason)<span class="device-unavailable">{{ $catalogDevice->reason }}</span>@endif
            </span>
          </label>
        @endforeach
        <label class="device-choice">
          <span class="device-tile-bar device-tile-bar-neutral" aria-hidden="true"></span>
          <input type="radio" name="catalog_device_id" value="" @checked(!old('catalog_device_id')) data-catalog-other-radio>
          <span class="device-choice-content"><strong>Other device</strong><span class="cap">Enter a device name or model</span></span>
        </label>
        @error('catalog_device_id')<span class="er-t">{{ $message }}</span>@enderror
      </fieldset>
      <div data-other-device-field @if(old('catalog_device_id')) hidden @endif>
        <label for="device-name">Device name or model</label>
        <input id="device-name" name="name" value="{{ old('name') }}" maxlength="100" placeholder="Enter a device name or model" @required(!old('catalog_device_id'))>
        @error('name')<span class="er-t">{{ $message }}</span>@enderror
      </div>
      <div>
        <label for="device-note">Notes for your landlord (optional)</label>
        <textarea id="device-note" class="device-note" name="note" rows="3" maxlength="500" placeholder="Add any details your landlord should know">{{ old('note') }}</textarea>
        @error('note')<span class="er-t">{{ $message }}</span>@enderror
      </div>
      <p class="cap">You don't set the price. Your landlord reviews it and sets the monthly fee.</p>
      <div class="acts">
        <button class="btn o" type="button" data-v2-close>Cancel</button>
        <button class="btn" type="submit">Send for review</button>
      </div>
    </form>
  </section>
</div>
<div id="tp" role="tooltip"></div>
@endsection
