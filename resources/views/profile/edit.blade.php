@extends($user->role === 'admin' ? 'layouts.admin' : 'layouts.tenant')
@section('title','Account profile · BoardersHub')
@section('content')
@php $backUrl = $user->role === 'admin' ? route('admin.dashboard') : route('tenant.home'); @endphp
<section class="mx-auto max-w-2xl">
  <a href="{{ $backUrl }}" class="text-sm font-semibold text-indigo-700">← Back to dashboard</a>
  <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <h1 class="text-xl font-bold text-slate-900">Account profile</h1>
    <p class="mt-1 text-sm text-slate-500">Update your account name and email address.</p>
    @if(session('status') === 'profile-updated')<p class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm font-medium text-emerald-700">Profile updated.</p>@endif
    <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
      @csrf @method('PATCH')
      <label class="block text-sm font-medium text-slate-700">Name
        <input name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
      </label>
      <label class="block text-sm font-medium text-slate-700">Email address
        <input name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('email')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
      </label>
      <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Save changes</button>
    </form>
  </section>
</section>
@endsection
