@extends('layouts.tenant')
@section('title','Home · BoardersHub')
@section('portal-v2','enabled')
@section('content')
@php
  $now = now();
  $periodStart = $billing['period']->copy()->startOfMonth();
  $periodEnd = $periodStart->copy()->addMonthNoOverflow();
  $periodDays = $periodStart->daysInMonth;
  $cycleDue = $billing['due']->copy();
  if ($billing['remaining'] <= 0 && $cycleDue->format('Y-m') !== $periodStart->format('Y-m')) {
      $cycleDue->subMonthNoOverflow();
  }
  $today = $now->copy()->startOfDay();
  $dueDay = min($periodDays, max(1, (int) $cycleDue->day));
  $daysUntilDue = (int) $today->diffInDays($cycleDue->copy()->startOfDay(), false);
  $isOverdue = $billing['remaining'] > 0 && $today->gt($cycleDue->copy()->startOfDay());
  $paidAmountCents = max(0, \App\Support\Billing::amountToCents($billing['paid_amount']));
  $totalBillCents = max(0, \App\Support\Billing::amountToCents($billing['amount']));
  $remaining = \App\Support\Billing::centsToAmount(max(0, \App\Support\Billing::amountToCents($billing['remaining'])));
  $totalBill = \App\Support\Billing::centsToAmount($totalBillCents);
  $paidRatio = $totalBillCents > 0 ? min(1, $paidAmountCents / $totalBillCents) : 0;
  $paidPercent = $totalBillCents > 0
      ? intdiv((2 * min($paidAmountCents, $totalBillCents) * 100) + $totalBillCents, 2 * $totalBillCents)
      : 0;
  $tankOffset = 190 - (176 * $paidRatio);
  $todayDay = min($periodDays, max(1, (int) $now->day));
  $timelineX = fn ($day) => 40 + ((min($periodDays, max(1, (int) $day)) - 1) * (640 / max(1, $periodDays - 1)));
  $todayX = $timelineX($todayDay);
  $dueX = $timelineX($dueDay);
  $graceDay = min($periodDays, max(1, $periodStart->diffInDays($graceEnd->copy()->startOfDay()) + 1));
  $graceX = $timelineX($graceDay);
  $graceBandX = min($dueX, $graceX);
  $graceBandWidth = max(0, abs($graceX - $dueX));
  $greeting = $now->hour < 12 ? 'Good morning' : ($now->hour < 18 ? 'Good afternoon' : 'Good evening');
  $billBadge = $remaining <= 0 ? 'ok' : ($isOverdue ? 'er' : 'wn');
  $billLabel = $remaining <= 0 ? 'Paid' : ($isOverdue ? 'Overdue' : 'Pending');
  $timelineEvents = [[
      'day' => 1,
      'label' => 'Bill charged',
      'date' => $periodStart->format('M j'),
      'detail' => 'Monthly boarder bill · ₱'.number_format($totalBill, 2),
      'color' => 'var(--lk)',
      'kind' => 'rent',
  ]];
  $olderOpenRepairCount = $periodRepairs->filter(
      fn ($repair) => $repair->status !== 'resolved' && $repair->created_at->lt($periodStart)
  )->count();
  foreach ($periodPayments as $periodPayment) {
      if ($periodPayment->created_at->lt($periodStart)) {
          continue;
      }
      $paymentDay = $periodPayment->created_at->gte($periodEnd)
          ? $periodDays
          : $periodStart->diffInDays($periodPayment->created_at->copy()->startOfDay()) + 1;
      $paymentMethod = match($periodPayment->payment_method) {
          'e_wallet' => 'GCash',
          'bank_transfer' => 'Bank transfer',
          'maya' => 'Maya',
          default => ucwords(str_replace('_', ' ', $periodPayment->payment_method)),
      };
      $paymentTone = match($periodPayment->status) {
          'verified' => 'var(--ok)',
          'rejected' => 'var(--er)',
          default => 'var(--wn)',
      };
      $timelineEvents[] = [
          'day' => $paymentDay,
          'label' => $paymentMethod.' · '.ucfirst($periodPayment->status),
          'date' => $periodPayment->created_at->format('M j'),
          'detail' => $paymentMethod.' · '.ucfirst($periodPayment->status).' · ₱'.number_format($periodPayment->amount, 2).' · '.$periodPayment->created_at->format('M j, Y · g:i A'),
          'color' => $paymentTone,
          'kind' => 'payment',
      ];
  }
  foreach ($periodRepairs as $periodRepair) {
      if ($periodRepair->created_at->lt($periodStart)) {
          continue;
      }
      $repairDay = $periodRepair->created_at->gte($periodEnd)
          ? $periodDays
          : $periodStart->diffInDays($periodRepair->created_at->copy()->startOfDay()) + 1;
      $repairCategory = \App\Models\MaintenanceRequest::CATEGORIES[$periodRepair->issue_category] ?? $periodRepair->issue_category;
      $repairTone = $periodRepair->status === 'resolved' ? 'var(--ok)' : ($periodRepair->status === 'in_progress' ? 'var(--lk)' : 'var(--wn)');
      $timelineEvents[] = [
          'day' => $repairDay,
          'label' => 'Repair · '.$repairCategory,
          'date' => $periodRepair->created_at->format('M j'),
          'detail' => 'Repair · '.$repairCategory.' · '.ucfirst(str_replace('_', ' ', $periodRepair->status)).' · '.$periodRepair->created_at->format('M j, Y · g:i A'),
          'color' => $repairTone,
          'kind' => 'repair',
      ];
  }
  $eventLanes = [-48, 48, -86, 86, -124, 124];
@endphp
<section class="hm">
  <div class="card tkc" data-s>
    <p class="lbl">{{ $now->locale('en')->translatedFormat('l, F j') }}</p>
    <h1 style="font-size:26px">{{ $greeting }}, {{ $user->name }}</h1>
    <p class="cap">
      @if($remaining <= 0)
        Your rent is fully paid. Thank you!
      @elseif($isOverdue)
        Your rent payment is overdue. A balance remains to be paid.
      @elseif($billing['status'] === 'pending')
        Your payment is pending landlord verification.
      @elseif($daysUntilDue > 0)
        Your balance is pending. Payment date is in {{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }}.
      @else
        Your balance is pending for today's payment date.
      @endif
    </p>

    <div class="tank" data-tank="{{ number_format($paidRatio, 4, '.', '') }}">
      <svg viewBox="0 0 200 200" role="img" aria-label="{{ $paidPercent }} percent of rent paid">
        <defs><clipPath id="cc"><circle cx="100" cy="100" r="88"/></clipPath></defs>
        <circle cx="100" cy="100" r="92" fill="var(--bg)" stroke="var(--bd)" stroke-width="6"/>
        <g clip-path="url(#cc)">
          <g class="lv" data-tank-level style="transform:translateY(190px)">
            <g class="w2" opacity=".35"><path d="M-200 12Q-150 -4-100 12T0 12T100 12T200 12T300 12T400 12V300H-200Z" fill="var(--lk)"/></g>
            <g class="w"><path d="M-200 14Q-150 30-100 14T0 14T100 14T200 14T300 14T400 14V300H-200Z" fill="var(--lk)" opacity=".6"/></g>
          </g>
        </g>
      </svg>
      <div class="tc"><b>{{ $paidPercent }}%</b><span>of rent paid</span></div>
    </div>

    <div class="rem">
      <p class="cap">Remaining balance</p>
      <div class="odo" data-odo="{{ number_format($remaining, 2, '.', '') }}" aria-label="Remaining balance ₱{{ number_format($remaining, 2) }}">₱{{ number_format($remaining, 2) }}</div>
      <p class="cap" style="margin:6px 0 10px">of ₱{{ number_format($totalBill, 2) }} this month</p>
      <span class="bdg {{ $billBadge }}">{{ $billLabel }}</span>
      <p class="cap" style="margin-top:8px">Payment date {{ $cycleDue->format('M j') }} · Grace period ends {{ $graceEnd->format('M j') }}</p>
    </div>
    <div class="acts">
      <a class="btn" href="{{ route('tenant.billing') }}" style="flex:1;min-height:48px">Pay rent</a>
      <a class="btn o" href="{{ route('tenant.billing') }}" style="min-height:48px">View bill</a>
    </div>
  </div>

  <div class="col">
    <div class="card" data-s>
      <div class="hd">
        <div>
          <h2 class="ct">Your {{ $periodStart->format('F') }} stay</h2>
          <p class="cap">{{ $periodStart->format('M j') }} – {{ $periodEnd->format('M j, Y') }} · hover or tap a dot for details<span class="tlh"> · swipe sideways</span></p>
        </div>
      </div>
      <div class="tlw" data-timeline>
        <svg viewBox="0 0 720 230" role="img" aria-label="Timeline of rent charge, available payment and repair records for {{ $periodStart->format('F Y') }}">
          <line class="ax" x1="40" x2="680" y1="115" y2="115"/>
          @if($graceBandWidth > 0)
            <rect x="{{ number_format($graceBandX, 2, '.', '') }}" y="105" width="{{ number_format($graceBandWidth, 2, '.', '') }}" height="20" rx="6" fill="var(--wn)" opacity=".2" aria-label="Grace period from {{ $cycleDue->format('M j') }} to {{ $graceEnd->format('M j') }}"/>
          @endif
          <line class="axp" data-timeline-progress x1="40" x2="{{ number_format($todayX, 2, '.', '') }}" y1="115" y2="115"/>
          <line x1="{{ number_format($dueX, 2, '.', '') }}" x2="{{ number_format($dueX, 2, '.', '') }}" y1="62" y2="168" stroke="var(--wn)" stroke-width="2" stroke-dasharray="4 4"/>
          @foreach([1, 5, 10, 15, 20, 25, $periodDays] as $tick)
            @if($tick <= $periodDays)
              <text class="tt" x="{{ number_format($timelineX($tick), 2, '.', '') }}" y="148" text-anchor="middle">{{ $tick }}</text>
            @endif
          @endforeach
          <text class="td" x="{{ number_format($dueX, 2, '.', '') }}" y="208" text-anchor="middle">Payment {{ $cycleDue->format('M j') }}</text>
          <text class="tt" x="680" y="222" text-anchor="end">Grace ends {{ $graceEnd->format('M j') }}</text>
          @foreach($timelineEvents as $event)
            @php($eventX = $timelineX($event['day']))
            @php($eventY = 115 + $eventLanes[$loop->index % count($eventLanes)])
            <g class="dot" tabindex="0" role="button" style="--i:{{ $loop->index }}" data-w="{{ $event['detail'] }}" data-d="{{ $event['date'] }}" aria-label="{{ $event['date'] }}: {{ $event['detail'] }}">
              <line x1="{{ number_format($eventX, 2, '.', '') }}" x2="{{ number_format($eventX, 2, '.', '') }}" y1="115" y2="{{ $eventY }}" stroke="{{ $event['color'] }}" stroke-width="2" opacity=".6"/>
              <circle cx="{{ number_format($eventX, 2, '.', '') }}" cy="{{ $eventY }}" r="9" fill="var(--sf)" stroke="{{ $event['color'] }}" stroke-width="4"/>
              <text class="tn" x="{{ number_format($eventX + 14, 2, '.', '') }}" y="{{ $eventY + 4 }}">{{ $event['label'] }}</text>
            </g>
          @endforeach
          <line x1="{{ number_format($todayX, 2, '.', '') }}" x2="{{ number_format($todayX, 2, '.', '') }}" y1="30" y2="115" stroke="var(--lk)" stroke-dasharray="4 4"/>
          <circle class="pul" cx="{{ number_format($todayX, 2, '.', '') }}" cy="115" r="8" fill="var(--lk)"/>
          <circle cx="{{ number_format($todayX, 2, '.', '') }}" cy="115" r="8" fill="var(--lk)"/>
          <text class="td" x="{{ number_format($todayX, 2, '.', '') }}" y="22" text-anchor="middle" style="fill:var(--lk)">Today</text>
        </svg>
      </div>
      @if($periodPayments->isEmpty())<p class="cap" style="margin-top:8px">No payment records for this billing period yet.</p>@endif
      @if($periodRepairs->isEmpty())<p class="cap" style="margin-top:4px">No repair requests for this billing period.</p>@endif
      @if($olderOpenRepairCount > 0)
        <p class="cap" style="margin-top:4px">{{ $olderOpenRepairCount }} older open repair request{{ $olderOpenRepairCount === 1 ? '' : 's' }} from before this period.</p>
      @endif
      <p class="cap" style="margin-top:8px">Grace period ends {{ $graceEnd->format('M j, Y') }}.</p>
    </div>

    <div class="jump" data-s>
      <a class="jt" href="{{ route('tenant.billing') }}">
        <span class="lbl">Billing</span>
        <b data-odo="{{ number_format($remaining, 2, '.', '') }}">₱{{ number_format($remaining, 2) }}</b>
        <span class="cap">remaining to pay · view bill</span>
      </a>
      <a class="jt" href="{{ route('tenant.map') }}">
        <span class="lbl">Room</span>
        @if($bed)
          <b>Room {{ $bed->room->number }}</b>
          <span class="cap">Bed {{ $bed->bed_no }} · {{ $bed->bunk === 'A' ? 'Top bunk' : ($bed->bunk === 'B' ? 'Bottom bunk' : 'Assigned bed') }}</span>
        @else
          <b>Room assignment pending</b>
          <span class="cap">Room and bed details will appear here</span>
        @endif
      </a>
      <a class="jt" href="{{ route('tenant.maintenance') }}">
        <span class="lbl">Repairs</span>
        <b>{{ $openRepairCount }} open</b>
        <span class="cap">{{ $openRepairCount === 0 ? 'No open repair requests' : 'Track your unresolved reports' }}</span>
      </a>
    </div>
  </div>
</section>
<div id="tp" role="tooltip"></div>
@endsection
