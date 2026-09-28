@extends('layouts.tenant')
@section('title','Billing · BoardersHub')
@section('content')
<section class="rounded-2xl bg-white p-4 shadow-sm">
  <div class="flex items-center justify-between"><p class="font-bold">{{ $billing['period']->format('F Y') }}</p><x-badge :status="$billing['status']"/></div>
  <p class="mt-2 text-3xl font-extrabold">₱{{ number_format($billing['amount']) }}</p>
  <p class="text-sm text-slate-500">Due {{ $billing['due']->format('M j, Y') }}</p>
</section>

@if(! in_array($billing['status'],['paid','pending']) && $user->bed)
<section class="space-y-3 rounded-2xl bg-white p-4 shadow-sm">
  <p class="font-bold">Pay with GCash</p>
  @if($gcash['number'])
    <div class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 text-sm">
      @if($gcash['qr'])<img src="{{ asset('storage/'.$gcash['qr']) }}" class="h-24 w-24 rounded-lg bg-white object-contain" alt="GCash QR">@endif
      <div><p class="text-xs text-slate-500">Send to</p><p class="text-lg font-bold tracking-wide">{{ $gcash['number'] }}</p><p class="text-xs">{{ $gcash['name'] }}</p></div>
    </div>
  @else <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-700">Your landlord hasn't set up GCash yet. You can pay in cash.</p> @endif
  <form method="POST" action="{{ route('tenant.payments.store') }}" enctype="multipart/form-data" class="space-y-3">@csrf
    <input name="amount" type="number" step="0.01" inputmode="decimal" value="{{ old('amount',$billing['amount']) }}" required placeholder="Amount (₱)" class="w-full rounded-xl border-slate-300 text-sm">
    <input name="gcash_reference" value="{{ old('gcash_reference') }}" required maxlength="30" inputmode="numeric" placeholder="GCash reference no." class="w-full rounded-xl border-slate-300 text-sm">
    <label class="block rounded-xl border border-dashed border-slate-300 p-3 text-sm text-slate-600">📎 Screenshot of receipt
      <input name="receipt" type="file" accept="image/*" required class="mt-2 block w-full text-xs"></label>
    <button class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white">Submit payment</button>
  </form>
</section>
@endif

<section class="rounded-2xl bg-white p-4 shadow-sm">
  <p class="mb-2 font-bold">Payment history</p>
  @forelse($payments as $p)
    <div class="flex items-center justify-between border-t border-slate-100 py-3 first:border-0">
      <div><p class="text-sm font-semibold">{{ $p->period->format('F Y') }} · {{ strtoupper($p->payment_method) }}</p>
        <p class="text-xs text-slate-500">{{ $p->created_at->format('M j, g:i A') }}@if($p->gcash_reference) · Ref {{ $p->gcash_reference }}@endif</p></div>
      <div class="text-right"><p class="text-sm font-bold">₱{{ number_format($p->amount) }}</p><x-badge :status="$p->status"/></div>
    </div>
  @empty <p class="py-4 text-center text-sm text-slate-500">No payments yet.</p> @endforelse
</section>
@endsection
