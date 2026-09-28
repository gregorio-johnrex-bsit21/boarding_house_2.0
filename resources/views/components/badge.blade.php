@props(['status'])
@php
$m = [
 'paid'=>['Paid','bg-emerald-100 text-emerald-700'],'verified'=>['Verified','bg-emerald-100 text-emerald-700'],
 'resolved'=>['Resolved','bg-emerald-100 text-emerald-700'],'available'=>['Available','bg-emerald-100 text-emerald-700'],
 'pending'=>['Pending','bg-amber-100 text-amber-700'],'due_soon'=>['Due soon','bg-orange-100 text-orange-700'],
 'ready'=>['Ready','bg-yellow-100 text-yellow-700'],'overdue'=>['Overdue','bg-rose-100 text-rose-700'],
 'rejected'=>['Rejected','bg-rose-100 text-rose-700'],'occupied'=>['Occupied','bg-rose-100 text-rose-700'],
 'in_progress'=>['In progress','bg-sky-100 text-sky-700'],'reserved'=>['Reserved','bg-blue-100 text-blue-700'],
 'unpaid'=>['Unpaid','bg-slate-100 text-slate-600'],
];
[$label,$cls] = $m[$status] ?? [ucfirst($status),'bg-slate-100 text-slate-600'];
@endphp
<span {{ $attributes->merge(['class'=>"inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold $cls"]) }}>{{ $label }}</span>
