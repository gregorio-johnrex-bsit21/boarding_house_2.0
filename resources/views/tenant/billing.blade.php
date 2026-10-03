@extends('layouts.tenant')
@section('title','Billing history · BoardersHub')
@section('content')
<section class="space-y-4">
  <div><h1 class="text-xl font-bold text-slate-900">Payment history</h1><p class="mt-1 text-sm text-slate-500">Payments recorded by your landlord.</p></div>
  <div class="space-y-3">
    @forelse($payments as $p)
      @php
        $periodStart = $p->period_start ?? $p->period->copy()->startOfMonth();
        $periodEnd = $p->period_end ?? $p->period->copy()->startOfMonth()->addMonthNoOverflow();
      @endphp
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0"><h2 class="font-semibold text-slate-900">{{ $p->period->format('F Y') }}</h2><p class="mt-1 text-sm text-slate-500">{{ $periodStart->format('M j, Y') }} – {{ $periodEnd->format('M j, Y') }}</p><p class="mt-1 text-xs text-slate-500">{{ ucwords(str_replace('_', ' ', $p->payment_method)) }} · {{ $p->created_at->format('M j, Y · g:i A') }}</p>@if($p->gcash_reference)<p class="mt-1 break-all text-xs text-slate-500">Reference: {{ $p->gcash_reference }}</p>@endif</div>
          <div class="text-right"><p class="font-bold text-slate-900">₱{{ number_format($p->amount, 2) }}</p><x-badge :status="$p->status"/></div>
        </div>
        @if($p->tenant_signature)<a href="{{ asset('storage/'.$p->tenant_signature) }}" target="_blank" rel="noopener" class="mt-3 inline-flex text-xs font-semibold text-indigo-700 underline">View cash payment signature</a>@endif
      </article>
    @empty
      <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center"><p class="font-semibold text-slate-700">No payment history yet</p><p class="mt-1 text-sm text-slate-500">Payments will appear here after your landlord records them.</p></div>
    @endforelse
  </div>
</section>
@endsection
