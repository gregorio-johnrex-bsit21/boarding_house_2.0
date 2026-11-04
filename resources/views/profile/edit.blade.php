@extends($user->role === 'admin' ? 'layouts.admin' : 'layouts.tenant')
@section('title','Account profile · BoardersHub')
@section('portal-v2', $user->role === 'admin' ? '' : 'enabled')
@section('content')
@if($user->role !== 'admin')
  @php
    $accountBed = $user->bed;
    $accountRoomLabel = $accountBed?->room
        ? 'Room '.$accountBed->room->number.' · '.($accountBed->label ?: 'Bed '.$accountBed->bed_no)
        : 'Room assignment pending';
    $profileIncomplete = ! $user->phone || ! $user->emergency_contact_name || ! $user->emergency_contact_phone;
  @endphp
  <div data-s>
    <p class="lbl">Account</p>
    <h1>Account profile</h1>
    <p class="sub" style="margin-bottom:18px">Keep your details up to date. Your Boarder Dashboard ID preview updates as you type.</p>
  </div>
  @if($profileIncomplete)
    <div class="alb" role="status">
      <span aria-hidden="true" style="font-size:22px">!</span>
      <div style="flex:1 1 220px">
        <b>Your profile is incomplete</b>
        <p class="cap">Add your phone number and emergency contact details so your landlord can reach you.</p>
      </div>
      <a class="btn o s" href="#account-information">Add details</a>
    </div>
  @endif

  <div class="ac">
    <aside class="idw" data-s aria-label="Boarder ID card preview">
      <button class="idc" type="button" data-id-card aria-expanded="false" aria-label="Flip boarder ID card" style="width:100%;border:0;background:transparent;color:inherit;padding:0;text-align:left">
        <span class="fc">
          <span style="display:flex;justify-content:space-between;align-items:center">
            <span class="lbl">BoardersHub · Boarder</span>
            <span class="av2" data-id-initial>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
          </span>
          <span><span class="nm" data-id-name>{{ $user->name }}</span><span class="cap" data-id-email style="display:block;word-break:break-all">{{ $user->email }}</span></span>
          <span style="display:flex;justify-content:space-between;gap:8px;font-weight:700"><span>{{ $accountRoomLabel }}</span><span class="cap">Tap to flip</span></span>
        </span>
        <span class="fc bk2">
          <span class="mag" aria-hidden="true"></span>
          <span><span class="lbl">Phone</span><span data-id-phone style="display:block">{{ $user->phone ?: 'Not set' }}</span></span>
          <span><span class="lbl">Emergency contact</span><span data-id-emergency style="display:block">{{ $user->emergency_contact_name ?: ($user->emergency_contact_phone ?: 'Not set') }}@if($user->emergency_contact_name && $user->emergency_contact_phone) · {{ $user->emergency_contact_phone }}@endif</span></span>
        </span>
      </button>
      <button class="btn o s" type="button" data-id-card-toggle style="width:100%;margin-top:14px">Flip card</button>
      <p class="cap" style="margin:10px 4px 0">A preview of the information saved to your account.</p>
    </aside>

    <div class="col">
      <section class="card" data-s id="account-information">
        <div class="hd">
          <div><h2 class="ct">Personal information</h2><p class="cap">Your profile details and emergency contact.</p></div>
          <span class="bdg wn" data-profile-unsaved hidden>Unsaved changes</span>
        </div>
        @if(session('status') === 'profile-updated')<p class="bdg ok" role="status" style="margin-bottom:14px">Profile updated.</p>@endif
        <form method="POST" action="{{ route('profile.update') }}" class="f account-form" data-id-preview-form data-profile-edit-form>
          @csrf
          @method('PATCH')
          <div>
            <label for="profile-name">Name</label>
            <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" data-id-preview="name">
            @error('name')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div>
            <label for="profile-email">Email address</label>
            <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" data-id-preview="email">
            @error('email')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div>
            <label for="profile-phone">Phone number</label>
            <input id="profile-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone', $user->phone) }}" data-id-preview="phone">
            @error('phone')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div>
            <label for="emergency-contact-name">Emergency contact name</label>
            <input id="emergency-contact-name" name="emergency_contact_name" autocomplete="off" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" data-id-preview="emergency">
            @error('emergency_contact_name')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div>
            <label for="emergency-contact-phone">Emergency contact number</label>
            <input id="emergency-contact-phone" name="emergency_contact_phone" type="tel" autocomplete="off" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}" data-id-preview="emergency-phone">
            @error('emergency_contact_phone')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div class="acts">
            <button class="btn o" type="button" data-profile-discard hidden>Discard changes</button>
            <button class="btn" type="submit">Save changes</button>
          </div>
        </form>
      </section>

      <section class="card" data-s>
        <h2 class="ct" style="margin-bottom:6px">Change password</h2>
        <p class="cap" style="margin-bottom:14px">Your current password and new password are checked by Laravel when submitted.</p>
        @if(session('status') === 'password-updated')<p class="bdg ok" role="status" style="margin-bottom:14px">Password updated.</p>@endif
        <form method="POST" action="{{ route('password.update') }}" class="f account-form" data-v2-validate data-v2-password-strength>
          @csrf
          @method('PUT')
          <div>
            <label for="current_password">Current password</label>
            <div class="pw"><input id="current_password" name="current_password" type="password" autocomplete="current-password" required><button class="btn o s" type="button" data-v2-password-toggle>Show</button></div>
            @error('current_password', 'updatePassword')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div>
            <label for="password">New password</label>
            <div class="pw"><input id="password" name="password" type="password" autocomplete="new-password" required><button class="btn o s" type="button" data-v2-password-toggle>Show</button></div>
            @error('password', 'updatePassword')<span class="er-t">{{ $message }}</span>@enderror
          </div>
          <div>
            <label for="password_confirmation">Confirm new password</label>
            <div class="pw"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><button class="btn o s" type="button" data-v2-password-toggle>Show</button></div>
          </div>
          <div class="sgt" data-password-strength aria-hidden="true"><i></i><i></i><i></i><i></i></div>
          <ul class="rl" data-password-rules>
            <li data-rule="current">Enter your current password</li>
            <li data-rule="length">At least 8 characters</li>
            <li data-rule="letters">Has a letter and a number</li>
            <li data-rule="match">New passwords match</li>
            <li data-rule="different">Different from your current password</li>
          </ul>
          <p class="cap">These are client-side hints; Laravel remains authoritative for password validation.</p>
          <button class="btn" type="submit" data-password-submit disabled>Update password</button>
        </form>
      </section>

    </div>
  </div>
@else
  @php($backUrl = route('admin.dashboard'))
  <section class="mx-auto" style="max-width:520px">
    <a href="{{ $backUrl }}" class="text-sm font-semibold text-indigo-700">← Back to dashboard</a>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
      <h1 class="text-xl font-bold text-slate-900">Account profile</h1>
      <p class="mt-1 text-sm text-slate-500">Update your account name and email address.</p>
      @if(session('status') === 'profile-updated')<p role="status" class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm font-medium text-emerald-700">Profile updated.</p>@endif
      <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('PATCH')
        <div>
          <label class="block text-sm font-medium text-slate-700" for="profile-name">Name</label>
          <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
          @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="profile-email">Email address</label>
          <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
          @error('email')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
        </div>
        <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Save changes</button>
      </form>
    </section>
  </section>
@endif
@endsection
