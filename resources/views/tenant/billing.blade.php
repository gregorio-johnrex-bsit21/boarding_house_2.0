@extends('layouts.tenant')
@section('title','Billing · BoardersHub')
@section('portal-v2','enabled')
@section('content')
@php
  $toCents = fn ($amount) => \App\Support\Billing::amountToCents($amount);
  $fromCents = fn ($cents) => \App\Support\Billing::centsToAmount((int) $cents);
  $barColors = ['rent' => 'var(--lk)', 'device' => 'var(--ok)'];
  $periodStart = $billing['period']->copy()->startOfMonth();
  $periodEnd = $periodStart->copy()->addMonthNoOverflow();
  $periodDays = $periodStart->daysInMonth;
  $currentPeriod = now()->format('Y-m');
  $cyclePayments = $payments->filter(fn ($payment) => $payment->period?->format('Y-m') === $selectedPeriod);
  $verifiedPayments = $cyclePayments->where('status', 'verified');
  $paidTotalCents = $toCents($billing['verified_total']);
  $totalCents = max(0, $toCents($billing['amount']));
  $balanceCents = max(0, $toCents($billing['remaining']));
  $balanceAmount = $fromCents($balanceCents);
  $dueDate = $periodStart->copy()->day(min(max(1, (int) $user->due_day), $periodDays));
  $graceDays = max(0, (int) \App\Models\Setting::get('due.grace_period_days', 3));
  $graceUntil = $dueDate->copy()->addDays($graceDays);
  $stamp = $paidTotalCents >= $totalCents
      ? ['PAID', 's-ok']
      : ($balanceCents > 0 && now()->startOfDay()->gt($graceUntil)
          ? ['OVERDUE', 's-er']
          : ['PENDING', 's-wn']);
  $periodOptions = $paymentPeriods->prepend($currentPeriod)->unique()->sortDesc()->values();
  $hasPastCycles = $periodOptions->contains(fn ($period) => $period !== $currentPeriod);
  $resubmitPayment = $payments->firstWhere('id', (int) request('resubmit'));
  if ($resubmitPayment?->status !== 'rejected' || $resubmitPayment?->period?->format('Y-m') !== $selectedPeriod) {
      $resubmitPayment = null;
  }
  $paymentMethod = old('payment_method', $resubmitPayment?->payment_method === 'e_wallet' ? 'gcash' : ($resubmitPayment?->payment_method ?? 'gcash'));
  $amountValue = old('amount', $resubmitPayment?->amount ?? $balanceAmount);
  $referenceValue = old('reference_number', $resubmitPayment?->gcash_reference ?? '');
  $amountMax = $balanceCents > 10000000 ? 100000 : $balanceAmount;
  $daysElapsed = $selectedPeriod === $currentPeriod ? min($periodDays, max(1, (int) now()->day)) : $periodDays;
  $xForDay = fn ($day) => 50 + ((min($periodDays, max(1, (int) $day)) - 1) * (450 / max(1, $periodDays - 1)));
  $yForBalance = fn ($value) => 20 + (1 - (min($totalCents, max(0, (int) $value)) / max(1, $totalCents))) * 140;
  $chartPoints = [[1, $totalCents]];
  $runningBalance = $totalCents;
  foreach ($cyclePayments->sortBy('created_at') as $chartPayment) {
      if ($chartPayment->status !== 'verified') {
          continue;
      }
      $chartDay = $chartPayment->created_at->lt($periodStart)
          ? 1
          : ($chartPayment->created_at->gte($periodEnd) ? $periodDays : $chartPayment->created_at->day);
      $chartPoints[] = [$chartDay, $runningBalance];
      $runningBalance = max(0, $runningBalance - $toCents($chartPayment->amount));
      $chartPoints[] = [$chartDay, $runningBalance];
  }
  $chartPoints[] = [$daysElapsed, $runningBalance];
  $chartPath = collect($chartPoints)->map(
      fn ($point, $index) => ($index === 0 ? 'M' : 'L')
          .$xForDay($point[0]).' '.$yForBalance($point[1])
  )->implode('');
@endphp

<div class="bl">
  <div class="rcw" data-s>
    <div class="rcpt">
      <h2>BOARDERSHUB</h2>
      <p class="c">STATEMENT · {{ strtoupper($periodStart->format('F Y')) }}</p>
      <p class="c">
        @if($user->bed)
          Room {{ $user->bed->room->number }} · Bed {{ $user->bed->bed_no }} · {{ $user->bed->bunk === 'A' ? 'Top' : 'Bottom' }} · {{ $user->name }}
        @else
          {{ $user->name }} · No bed assigned
        @endif
      </p>
      <div class="stamp {{ $stamp[1] }}">{{ $stamp[0] }}</div>
      <div style="height:42px"></div>
      @foreach($billing['lines'] as $billLine)
        <div class="rr"><span>{{ $billLine['label'] }}</span><span>₱{{ number_format($billLine['amount'], 2) }}</span></div>
      @endforeach
      <div class="rr t"><span>TOTAL</span><span>₱{{ number_format($fromCents($totalCents), 2) }}</span></div>
      <div class="rr"><span>Paid</span><span>₱{{ number_format($fromCents($paidTotalCents), 2) }}</span></div>
      <div class="rr t"><span>BALANCE</span><span>₱{{ number_format($balanceAmount, 2) }}</span></div>
      <p class="c" style="margin-top:12px">Payment date {{ $dueDate->format('M j, Y') }} · Grace until {{ $graceUntil->format('M j, Y') }}</p>
      <div class="bar2"></div>
    </div>
    <div class="acts" style="margin-top:26px">
      <button class="btn" type="button" data-v2-open data-v2-title="{{ $resubmitPayment ? 'Resubmit payment' : 'Pay rent' }}" style="flex:1">
        {{ $resubmitPayment ? 'Resubmit payment' : 'Submit payment' }}
      </button>
    </div>
  </div>

  <div class="col">
    <div class="card" data-s id="spl">
      <div class="hd" style="margin-bottom:8px">
        <div><h2 class="ct">What your bill is made of</h2><p class="cap">Total ₱{{ number_format($fromCents($totalCents), 2) }} for {{ $periodStart->format('F Y') }}</p></div>
      </div>
      @php
        $lineCents = collect($billing['lines'])->map(fn ($line) => $toCents($line['amount']));
      @endphp
      <div class="sb2">
        @foreach($billing['lines'] as $index => $billLine)
          @php
            $lineShare = $totalCents > 0 ? $lineCents[$index] / $totalCents : 0;
          @endphp
          <i style="flex:{{ $lineShare }};background:{{ $barColors[$billLine['type']] ?? 'var(--lk)' }}"></i>
        @endforeach
      </div>
      @foreach($billing['lines'] as $index => $billLine)
        @php
          $linePercent = $totalCents > 0 ? round(($lineCents[$index] / $totalCents) * 100) : 0;
        @endphp
        <div class="li">
          <span style="display:flex;align-items:center;gap:10px"><u style="width:12px;height:12px;border-radius:4px;background:{{ $barColors[$billLine['type']] ?? 'var(--lk)' }}"></u>{{ $billLine['label'] }}</span>
          <span><b>₱{{ number_format($billLine['amount'], 2) }}</b> <span class="cap">{{ $linePercent }}%</span></span>
        </div>
      @endforeach
    </div>

    <div class="card ch" data-s>
      <div class="hd">
        <div><h2 class="ct">Balance this month</h2><p class="cap">Only verified payments lower your balance</p></div>
        <div style="min-width:170px">
          @if($hasPastCycles)
            <label for="billing-period">Past billing cycles</label>
            <select id="billing-period" onchange="location.href='{{ route('tenant.billing') }}?period='+this.value">
              @foreach($periodOptions as $periodOption)
                <option value="{{ $periodOption }}" @selected($selectedPeriod === $periodOption)>{{ \Carbon\Carbon::createFromFormat('!Y-m', $periodOption)->format('F Y') }}</option>
              @endforeach
            </select>
          @else
            <p class="cap">Billing cycle · {{ $periodStart->format('F Y') }}</p>
          @endif
        </div>
      </div>
      <svg viewBox="0 0 520 190" role="img" aria-label="Balance over {{ $periodStart->format('F Y') }}">
        <g class="tt">
          <text x="44" y="24" text-anchor="end">₱{{ number_format($fromCents($totalCents), 0) }}</text>
          <text x="44" y="94" text-anchor="end">₱{{ number_format($fromCents(intdiv($totalCents, 2)), 0) }}</text>
          <text x="44" y="164" text-anchor="end">₱0</text>
        </g>
        <line x1="50" x2="500" y1="20" y2="20" stroke="var(--bd)"/>
        <line x1="50" x2="500" y1="90" y2="90" stroke="var(--bd)" stroke-dasharray="4 4"/>
        <line x1="50" x2="500" y1="160" y2="160" stroke="var(--bd)"/>
        <path class="ca" d="{{ $chartPath }}L{{ $xForDay($daysElapsed) }} 160L50 160Z" style="animation-name:fd2"/>
        <path class="cl" pathLength="1" d="{{ $chartPath }}"/>
        @foreach($chartPoints as $point)
          <circle cx="{{ number_format($xForDay($point[0]), 2, '.', '') }}" cy="{{ number_format($yForBalance($point[1]), 2, '.', '') }}" r="4" fill="var(--sf)" stroke="var(--lk)" stroke-width="2.5"/>
        @endforeach
        <text class="tt" x="50" y="182">{{ $periodStart->format('M j') }}</text>
        <text class="tt" x="{{ number_format($xForDay($dueDate->day), 2, '.', '') }}" y="182" text-anchor="middle">Payment {{ $dueDate->format('M j') }}</text>
        <text class="tt" x="490" y="182" text-anchor="end">{{ $periodEnd->format('M j') }}</text>
      </svg>
    </div>

    <div class="card" data-s>
      <h2 class="ct" style="margin-bottom:6px">Ledger</h2>
      <div class="vt">
        @foreach($billing['lines'] as $billLine)
          <div class="li" style="--c:{{ $barColors[$billLine['type']] ?? 'var(--lk)' }}">
            <div><b>{{ $billLine['label'] }}</b><p class="cap">{{ $periodStart->format('M j') }} · charge</p></div>
            <b>₱{{ number_format($billLine['amount'], 2) }}</b>
          </div>
        @endforeach
        @forelse($cyclePayments->sortBy('created_at') as $ledgerPayment)
          @php
            $methodLabel = match($ledgerPayment->payment_method) { 'e_wallet' => 'GCash', 'bank_transfer' => 'Bank transfer', 'cash' => 'Cash', 'maya' => 'Maya', default => ucwords(str_replace('_', ' ', $ledgerPayment->payment_method)) };
          @endphp
          <div class="li" style="--c:{{ $ledgerPayment->status === 'verified' ? 'var(--ok)' : ($ledgerPayment->status === 'rejected' ? 'var(--er)' : 'var(--wn)') }}">
            <div>
              <b>{{ $methodLabel }} · {{ ucfirst($ledgerPayment->status) }}</b>
              <p class="cap">{{ $ledgerPayment->created_at->format('M j, Y · g:i A') }}@if($ledgerPayment->gcash_reference) · Ref {{ $ledgerPayment->gcash_reference }}@endif</p>
              @if($ledgerPayment->status === 'rejected')
                <p class="cap" style="color:var(--er)">Reason: {{ $ledgerPayment->note ?: 'not recorded' }}</p>
                <a class="btn o s" href="{{ route('tenant.billing', ['pay' => 1, 'resubmit' => $ledgerPayment->id, 'period' => $selectedPeriod]) }}" style="margin-top:8px">Resubmit</a>
              @elseif($ledgerPayment->status === 'pending')
                <p class="cap">Pending review; this does not reduce your balance.</p>
              @endif
              @if($ledgerPayment->receipt_image)
                <a class="btn g s" href="{{ asset('storage/'.$ledgerPayment->receipt_image) }}" download style="margin-top:6px">↓ Download receipt</a>
              @endif
            </div>
            <b>₱{{ number_format($ledgerPayment->amount, 2) }}</b>
          </div>
        @empty
          <p class="cap">No payments recorded for this billing period.</p>
        @endforelse
      </div>
    </div>

    <div class="card" data-s>
      <h2 class="ct" style="margin-bottom:6px">Payments</h2>
      <div class="vt">
        @forelse($cyclePayments->sortByDesc('created_at') as $payment)
          @php
            $methodLabel = match($payment->payment_method) { 'e_wallet' => 'GCash', 'bank_transfer' => 'Bank transfer', 'cash' => 'Cash', 'maya' => 'Maya', default => ucwords(str_replace('_', ' ', $payment->payment_method)) };
          @endphp
          <div class="li" style="--c:{{ $payment->status === 'verified' ? 'var(--ok)' : ($payment->status === 'rejected' ? 'var(--er)' : 'var(--wn)') }}">
            <div>
              <b>{{ $methodLabel }} · {{ ucfirst($payment->status) }}</b>
              <p class="cap">{{ $payment->created_at->format('M j, Y · g:i A') }}@if($payment->gcash_reference) · Ref {{ $payment->gcash_reference }}@endif</p>
              @if($payment->status === 'rejected')<p class="cap" style="color:var(--er)">Reason: {{ $payment->note ?: 'not recorded' }}</p>@endif
              @if($payment->receipt_image)<a class="btn g s" href="{{ asset('storage/'.$payment->receipt_image) }}" download style="margin-top:6px">↓ Download receipt</a>@endif
            </div>
            <b>₱{{ number_format($payment->amount, 2) }}</b>
          </div>
        @empty
          <p class="cap">No payment history for this billing period yet.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

<div class="ov" id="ovl" @if(!request()->boolean('pay') && !$errors->any()) hidden @endif>
  <section class="shn" role="dialog" aria-modal="true" aria-labelledby="st">
    <div class="hd">
      <div><p class="lbl">PAYMENT</p><h2 class="ct" id="st">{{ $resubmitPayment ? 'Resubmit payment' : 'Pay rent' }}</h2></div>
      <button class="btn o s" type="button" data-v2-close aria-label="Close payment form">Close</button>
    </div>
    @if(! $user->bed)
      <p class="cap">Your landlord must assign a bed before you can submit rent.</p>
    @else
      <form method="POST" action="{{ route('tenant.billing.store') }}" enctype="multipart/form-data" class="f" data-v2-validate>
        @csrf
        <input type="hidden" id="payment-method" name="payment_method" value="{{ $paymentMethod }}">
        @if($resubmitPayment)<input type="hidden" name="resubmit_id" value="{{ $resubmitPayment->id }}">@endif
        <fieldset style="border:0;padding:0;margin:0">
          <legend class="lbl">Payment method</legend>
          <div class="cps">
            <button class="cp" type="button" data-payment-method="cash" aria-pressed="{{ $paymentMethod === 'cash' ? 'true' : 'false' }}">Cash</button>
            <button class="cp" type="button" data-payment-method="gcash" aria-pressed="{{ $paymentMethod === 'gcash' ? 'true' : 'false' }}">GCash</button>
            <button class="cp" type="button" data-payment-method="maya" aria-pressed="{{ $paymentMethod === 'maya' ? 'true' : 'false' }}">Maya</button>
            <button class="cp" type="button" data-payment-method="bank_transfer" aria-pressed="{{ $paymentMethod === 'bank_transfer' ? 'true' : 'false' }}">Bank transfer</button>
          </div>
          @error('payment_method')<span class="er-t">{{ $message }}</span>@enderror
        </fieldset>
        <div>
          <label for="pay-amount">Amount</label>
          <input id="pay-amount" name="amount" type="number" min="0.01" max="100000" step="0.01" inputmode="decimal" value="{{ $amountValue }}" data-balance-limit="{{ number_format($amountMax, 2, '.', '') }}" required>
          <p class="cap">Enter an amount up to your remaining balance of ₱{{ number_format($balanceAmount, 2) }}.</p>
          @error('amount')<span class="er-t">{{ $message }}</span>@enderror
        </div>
        <div>
          <label for="pay-reference">Reference number</label>
          <input id="pay-reference" name="reference_number" value="{{ $referenceValue }}" placeholder="e.g. 1234 567 890123" autocomplete="off" @required(in_array($paymentMethod, ['gcash', 'maya'], true))>
          @error('reference_number')<span class="er-t">{{ $message }}</span>@enderror
        </div>
        <div>
          <label for="pay-receipt">Upload screenshot of receipt</label>
          <input id="pay-receipt" type="file" name="receipt" accept="image/png,image/jpeg">
          <p class="cap">PNG or JPG, up to 5 MB</p>
          @error('receipt')<span class="er-t">{{ $message }}</span>@enderror
        </div>
        <p class="cap">Pending payments do not lower your balance until verified.</p>
        <div class="acts">
          <button class="btn o" type="button" data-v2-close>Cancel</button>
          <button class="btn" type="submit">Submit payment</button>
        </div>
      </form>
    @endif
  </section>
</div>
<div id="tp" role="tooltip"></div>
@endsection
