@extends('layouts.tenant')
@section('title','Repairs · BoardersHub')
@section('content')
<div class="flex items-center justify-between gap-3">
  <div><h1 class="font-bold text-slate-900">Repair requests</h1><p class="text-sm text-slate-500">Track your reports and their progress.</p></div>
  <button type="button" @click="report=true" class="shrink-0 rounded-xl bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white">Report issue</button>
</div>
@forelse($requests as $m)
  <article class="rounded-2xl bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-2">
      <div><p class="font-semibold">{{ \App\Models\MaintenanceRequest::CATEGORIES[$m->issue_category] ?? $m->issue_category }}</p>
        <p class="text-xs text-slate-500">{{ $m->created_at->diffForHumans() }} · {{ ucfirst($m->urgency) }} urgency</p></div>
      <x-badge :status="$m->status"/>
    </div>
    <p class="mt-2 text-sm text-slate-600">{{ $m->description }}</p>
    @if($m->image)<img src="{{ asset('storage/'.$m->image) }}" class="mt-2 h-32 w-full rounded-xl object-cover" alt="Photo">@endif
    @php $step = ['pending'=>1,'in_progress'=>2,'resolved'=>3][$m->status] ?? 1; @endphp
    <div class="mt-3 flex gap-1">@foreach([1,2,3] as $i)<span class="h-1.5 flex-1 rounded-full {{ $i <= $step ? 'bg-indigo-500' : 'bg-slate-200' }}"></span>@endforeach</div>
    <div class="mt-1 flex justify-between text-[10px] text-slate-400"><span>Sent</span><span>In progress</span><span>Fixed</span></div>
  </article>
@empty
  <div class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500">No repair requests yet. Use “Report issue” to tell your landlord about a problem.</div>
@endforelse
@endsection
