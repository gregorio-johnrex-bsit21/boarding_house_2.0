@extends('layouts.tenant')
@section('title','Repairs · BoardersHub')
@section('portal-v2','enabled')
@section('content')
@php
  $openRequests = $requests->where('status', '!=', 'resolved')->values();
  $resolvedRequests = $requests->where('status', 'resolved')->values();
  $selectedRepair = $openRequests->first() ?? $resolvedRequests->first();
  $repairStages = ['pending' => 'Submitted', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'];
  $repairCategoryLabel = fn ($category) => $category === 'wifi'
      ? 'Other issue'
      : (\App\Models\MaintenanceRequest::CATEGORIES[$category] ?? $category);
@endphp
<div class="hd" data-s>
  <div><p class="lbl">Repairs</p><h1>Repairs</h1><p class="sub">Tap a request to view its details. Your landlord updates the repair status.</p></div>
  <button class="btn" type="button" data-v2-open data-v2-title="Report an issue">Report issue</button>
</div>

@if($requests->isNotEmpty())
  <div data-repair-feature-container>
    @foreach($requests as $repair)
      @php
        $categoryLabel = $repairCategoryLabel($repair->issue_category);
        $urgencyLabel = ['low' => 'Low', 'normal' => 'Medium', 'urgent' => 'Urgent'][$repair->urgency] ?? ucfirst($repair->urgency);
        $stageTitle = $repairStages[$repair->status] ?? ucfirst(str_replace('_', ' ', $repair->status));
        $stageTone = match($repair->status) { 'resolved' => 'ok', 'in_progress' => 'in', default => 'wn' };
        $progressSegments = match($repair->status) {
            'resolved' => ['var(--lk)', 'var(--lk)', 'var(--lk)'],
            'in_progress' => ['var(--lk)', 'var(--lk)', 'var(--bd)'],
            default => ['var(--lk)', 'var(--bd)', 'var(--bd)'],
        };
      @endphp
      <article class="card rcd {{ $repair->id === $selectedRepair->id ? 'open' : '' }}" data-s data-repair-feature data-repair-panel="{{ $repair->id }}" aria-expanded="{{ $repair->id === $selectedRepair->id ? 'true' : 'false' }}" @if($repair->id !== $selectedRepair->id) hidden @endif style="margin-bottom:18px">
        <div class="hd" style="margin-bottom:6px">
          <div>
            <p class="lbl">{{ $repair->status === 'resolved' ? 'Finished repair' : 'Selected request' }}</p>
            <h2 class="ct" style="font-size:22px">{{ $categoryLabel }}</h2>
            <p class="cap">Reported {{ $repair->created_at->format('M j, Y') }} · {{ $urgencyLabel }}</p>
          </div>
          <span class="bdg {{ $stageTone }}">{{ $stageTitle }}</span>
        </div>
        <p class="clamp">{{ $repair->description }}</p>
        @if($repair->image)
          <a href="{{ asset('storage/'.$repair->image) }}" target="_blank" rel="noopener">
            <img src="{{ asset('storage/'.$repair->image) }}" alt="Photo attached to repair report" style="display:block;width:100%;max-height:250px;object-fit:cover;margin:8px 0 12px;border-radius:14px">
          </a>
        @endif
        <div class="xp" id="repair-details-{{ $repair->id }}">
          <div>
            <div class="repair-progress" role="img" aria-label="Repair progress: {{ $stageTitle }}">
              @foreach(['Submitted', 'In Progress', 'Resolved'] as $stageIndex => $stageLabel)
                @if($stageIndex > 0)<span class="repair-progress-line" style="--progress-color:{{ $progressSegments[$stageIndex] }}"></span>@endif
                <span class="repair-progress-stage">
                  <span class="repair-progress-track" style="--progress-color:{{ $progressSegments[$stageIndex] }}"></span>
                  <span class="repair-progress-label">{{ $stageLabel }}</span>
                  <span class="cap">
                    @if($stageIndex === 0)
                      {{ $repair->created_at->format('M j') }}
                    @elseif($stageIndex === 1)
                      {{ $repair->in_progress_at?->format('M j') ?? ($repair->status === 'pending' ? 'Waiting' : 'Time not recorded') }}
                    @else
                      {{ $repair->resolved_at?->format('M j') ?? 'Not resolved' }}
                    @endif
                  </span>
                </span>
              @endforeach
            </div>
          </div>
        </div>
        <button class="tapn" type="button" data-repair-toggle aria-controls="repair-details-{{ $repair->id }}" aria-expanded="{{ $repair->id === $selectedRepair->id ? 'true' : 'false' }}" style="width:100%;border:0;background:transparent;padding:0"></button>
      </article>
    @endforeach
  </div>

  <section class="card sec" data-s>
    <h2 class="ct" style="margin-bottom:10px">Open requests <span class="bdg wn" style="margin-left:6px">{{ $openRequests->count() }}</span></h2>
    @if($openRequests->isNotEmpty())
      <table class="tbl">
        <thead><tr><th>Issue</th><th>Urgency</th><th>Reported</th><th>Status</th></tr></thead>
        <tbody>
          @foreach($openRequests as $repair)
            @php
              $categoryLabel = $repairCategoryLabel($repair->issue_category);
              $urgencyLabel = ['low' => 'Low', 'normal' => 'Medium', 'urgent' => 'Urgent'][$repair->urgency] ?? ucfirst($repair->urgency);
              $statusLabel = $repairStages[$repair->status] ?? ucfirst(str_replace('_', ' ', $repair->status));
              $statusTone = $repair->status === 'in_progress' ? 'in' : 'wn';
            @endphp
            <tr class="rw" tabindex="0" data-repair-select="{{ $repair->id }}" data-cur="{{ $repair->id === $selectedRepair->id ? 'true' : 'false' }}" aria-controls="repair-details-{{ $repair->id }}">
              <td data-l="Issue"><b>{{ $categoryLabel }}</b><p class="cap">{{ $repair->description }}</p></td>
              <td data-l="Urgency">{{ $urgencyLabel }}</td>
              <td data-l="Reported">{{ $repair->created_at->format('M j, Y') }}</td>
              <td data-l="Status"><span class="bdg {{ $statusTone }}">{{ $statusLabel }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="em">No open repair requests. Everything is resolved.</div>
    @endif
  </section>

  <section class="card sec" data-s>
    <h2 class="ct" style="margin-bottom:6px">History <span class="bdg ok" style="margin-left:6px">{{ $resolvedRequests->count() }}</span></h2>
    @forelse($resolvedRequests as $repair)
      @php
        $categoryLabel = $repairCategoryLabel($repair->issue_category);
      @endphp
      <button class="li hrw" type="button" data-repair-select="{{ $repair->id }}" data-cur="{{ $repair->id === $selectedRepair->id ? 'true' : 'false' }}" aria-controls="repair-details-{{ $repair->id }}" style="width:100%;text-align:left;color:inherit;background:transparent;border:0;font:inherit">
        <span><b>{{ $categoryLabel }}</b><span class="cap" style="display:block">Reported {{ $repair->created_at->format('M j, Y') }} · Resolved {{ $repair->resolved_at?->format('M j, Y') ?? 'Date not recorded' }}</span></span>
        <span class="bdg ok">Resolved</span>
      </button>
    @empty
      <div class="em">No finished repairs yet. Completed repairs will appear here.</div>
    @endforelse
  </section>
@else
  <div class="card sec" data-s><div class="em">No repair requests yet. Report an issue if something needs fixing.</div></div>
@endif

<div class="ov" id="ovl" @if(!$errors->any()) hidden @endif>
  <section class="shn" role="dialog" aria-modal="true" aria-labelledby="st">
    <div class="hd"><div><p class="lbl">REPAIRS</p><h2 class="ct" id="st">Report an issue</h2></div><button class="btn o s" type="button" data-v2-close>Close</button></div>
    <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data" class="f repair-form" data-v2-validate>
      @csrf
      @php
        $selectedCategory = old('issue_category', 'pipes');
        if ($selectedCategory === 'wifi') {
            $selectedCategory = 'pipes';
        }
      @endphp
      <input type="hidden" id="repair-category" name="issue_category" value="{{ $selectedCategory }}">
      <input type="hidden" id="repair-urgency" name="urgency" value="{{ old('urgency', 'normal') }}">
      <fieldset style="border:0;padding:0;margin:0">
        <legend class="lbl">Issue category</legend>
        <div class="cps">
          @foreach(\App\Models\MaintenanceRequest::CATEGORIES as $value => $label)
            @if($value !== 'wifi')
              <button class="cp" type="button" data-repair-choice="category" data-value="{{ $value }}" aria-pressed="{{ $selectedCategory === $value ? 'true' : 'false' }}">{{ $label }}</button>
            @endif
          @endforeach
        </div>
        @error('issue_category')<span class="er-t">{{ $message }}</span>@enderror
      </fieldset>
      <fieldset style="border:0;padding:0;margin:0">
        <legend class="lbl">Urgency</legend>
        <div class="cps">
          @foreach(['low' => 'Low', 'normal' => 'Medium', 'urgent' => 'Urgent'] as $value => $label)
            <button class="cp" type="button" data-repair-choice="urgency" data-value="{{ $value }}" aria-pressed="{{ old('urgency', 'normal') === $value ? 'true' : 'false' }}">{{ $label }}</button>
          @endforeach
        </div>
        @error('urgency')<span class="er-t">{{ $message }}</span>@enderror
      </fieldset>
      <div>
        <label for="repair-description">What happened?</label>
        <textarea id="repair-description" name="description" rows="4" maxlength="1000" required placeholder="Describe the problem and where it is">{{ old('description') }}</textarea>
        @error('description')<span class="er-t">{{ $message }}</span>@enderror
      </div>
      <div>
        <label for="repair-image">Add a photo (optional)</label>
        <div class="repair-file-control">
          <label class="repair-file-button" for="repair-image">Choose photo</label>
          <span class="repair-file-name" data-file-name>No file chosen</span>
          <input id="repair-image" class="repair-file-native" type="file" name="image" accept="image/*">
        </div>
        <p class="cap">Choose an image from your device.</p>
        @error('image')<span class="er-t">{{ $message }}</span>@enderror
      </div>
      <div class="acts"><button class="btn o" type="button" data-v2-close>Cancel</button><button class="btn" type="submit">Send report</button></div>
    </form>
  </section>
</div>
<div id="tp" role="tooltip"></div>
@endsection
