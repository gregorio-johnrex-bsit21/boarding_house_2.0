@extends('layouts.admin')

@section('content')
<div style="margin-bottom: 30px;">
    <a href="{{ route('admin.tenants') }}" style="color: var(--secondary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Directory
    </a>
    <h1 style="color: var(--primary); font-weight: 800; margin: 0;">Register New Tenant</h1>
</div>

{{-- Global validation errors fallback alert block --}}
@if ($errors->any())
    <div style="background-color: #fff5f5; border: 1px solid #ff7675; color: #ff7675; padding: 15px; border-radius: 10px; max-width: 600px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; box-sizing: border-box;">
        <span style="display: block; margin-bottom: 5px;"><i class="fa-solid fa-triangle-exclamation"></i> Please resolve the following conflicts:</span>
        <ul style="margin: 0; padding-left: 20px; font-weight: 500;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="background: white; border-radius: 20px; padding: 40px; max-width: 600px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); box-sizing: border-box;">
    <form action="{{ route('admin.tenants.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
        @csrf
        
        {{-- SECTION: PROFILE DATA --}}
        <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe" 
                style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid @error('name') #ff7675 @else #dcdde1 @enderror; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            @error('name')
                <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
            @enderror
        </div>

        <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Contact Number</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 0912-345-6789" 
                style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid @error('phone') #ff7675 @else #dcdde1 @enderror; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            @error('phone')
                <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
            @enderror
        </div>

        <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Assign Room Number</label>
            <select name="room_id" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid @error('room_id') #ff7675 @else #dcdde1 @enderror; font-family: inherit; font-size: 0.95rem; background-color: white; box-sizing: border-box;">
                <option value="">Leave Unassigned / Select Later...</option>
                @foreach($availableRooms as $room)
                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        Room {{ $room->room_number }} (₱{{ number_format($room->price, 2) }}/mo)
                    </option>
                @endforeach
            </select>
            @error('room_id')
                <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
            @enderror
        </div>

        {{-- SECTION: ACCOUNT ACCESS LOG-IN DETAILS --}}
        <div style="border-top: 2px solid #f5f5f4; padding-top: 15px; margin-top: 5px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Portal Login Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="tenant@email.com" 
                style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid @error('email') #ff7675 @else #dcdde1 @enderror; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            @error('email')
                <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
            @else
                <small style="color: #636e72; font-size: 0.8rem; margin-top: 4px; display: block;">This will be their unique username for logging into the system.</small>
            @enderror
        </div>

        <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Portal Password</label>
            <input type="password" name="password" required placeholder="Minimum 6 characters" 
                style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid @error('password') #ff7675 @else #dcdde1 @enderror; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            @error('password')
                <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn-primary" style="background: var(--primary); color: white; padding: 14px; border-radius: 10px; border: none; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px;">
            <i class="fa-solid fa-user-plus"></i> Complete Registration & Provision Access
        </button>
    </form>
</div>
@endsection