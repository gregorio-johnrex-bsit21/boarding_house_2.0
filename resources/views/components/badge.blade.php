@props(['status'])
@php
$m = [
 'paid'=>['Paid','bg-emerald-700 text-white'],'partial'=>['Partially paid','bg-blue-700 text-white'],'verified'=>['Verified','bg-emerald-700 text-white'],
 'resolved'=>['Resolved','bg-emerald-700 text-white'],'available'=>['Available','bg-emerald-700 text-white'],
 'pending'=>['Pending','bg-transparent text-slate-900 italic shadow-none'],'due_soon'=>['Due soon','bg-transparent text-rose-600 shadow-none'],
 'due'=>['Due','bg-rose-700 text-white'],
 'ready'=>['Ready','bg-yellow-600 text-white'],'overdue'=>['Overdue','bg-rose-700 text-white'],
 'rejected'=>['Rejected','bg-rose-700 text-white'],'occupied'=>['Occupied','bg-rose-700 text-white'],
 'in_progress'=>['In progress','bg-sky-700 text-white'],'reserved'=>['Reserved','bg-blue-700 text-white'],
 'unpaid'=>['Unpaid','bg-transparent text-slate-900 shadow-none'],
];
[$label,$cls] = $m[$status] ?? [ucfirst($status),'bg-slate-100 text-slate-600'];
@endphp
<span {{ $attributes->merge(['class'=>"inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-semibold tracking-wide shadow-sm $cls"]) }}>{{ $label }}</span>
