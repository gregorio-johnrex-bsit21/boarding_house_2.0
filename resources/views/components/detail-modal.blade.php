@props(['title' => 'Details', 'open' => 'detailOpen'])

<div x-show="{{ $open }}" x-cloak @keydown.escape.window="{{ $open }}=false" @click.self="{{ $open }}=false" @click.stop
     style="inset:-12px 0 0" class="fixed z-[70] flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="{{ $title }}">
  <div class="my-auto w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl sm:p-6" @click.stop>
    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
      <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Details</p>
        <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $title }}</h3>
      </div>
      <button type="button" @click="{{ $open }}=false" class="shrink-0 rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Close details">✕</button>
    </div>
    <div class="mt-4">{{ $slot }}</div>
  </div>
</div>
