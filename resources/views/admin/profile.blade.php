@extends('layouts.admin')

<<<<<<< HEAD
@section('title', 'IPK Boardinghouse System - Admin Profile')

@section('content')
<style>
    /* Profile specific styles scoped to not interfere with your main layout */
    .profile-wrapper {
        padding-bottom: 50px; /* Ensures you can scroll past the bottom save button */
        max-width: 1000px;
    }
    .profile-card {
        background: #FFFFFF; /* 30% White */
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid #EFEFEF; /* 10% Light Grey */
        margin-bottom: 25px;
    }
    
    .form-group { margin-bottom: 20px; }
    .form-row { display: flex; gap: 20px; }
    .form-row .form-group { flex: 1; }
    
    label {
        display: block;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 800;
        color: #7f8c8d;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }
    
    /* Input sizing strictly set to 1rem to stop mobile browsers from zooming/warping the layout */
    .profile-input {
        width: 100%;
        padding: 14px 18px;
        border-radius: 10px;
        background-color: #FFFFFF;
        color: #3E2723; /* 60% Dominant Brown */
        font-size: 1rem; 
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .profile-input:focus {
        outline: none;
        background-color: #FFF8F6;
    }

    /* Custom Toggle Switches */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #bdc3c7; transition: .4s; border-radius: 34px;
    }
    .slider:before {
        position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px;
        background-color: white; transition: .4s; border-radius: 50%;
    }
    .toggle-switch input:checked + .slider { background-color: #3E2723; }
    .toggle-switch input:checked + .slider:before { transform: translateX(24px); }

    .submit-btn {
        width: 100%;
        background: #3E2723;
        color: #FFFFFF;
        border: none;
        padding: 16px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(62, 39, 35, 0.15);
    }
    .submit-btn:hover {
        background: #2D1B19;
        transform: translateY(-2px);
    }
</style>

<div class="profile-wrapper">
    <div style="margin-bottom: 30px;">
        <h1 style="color: #3E2723; font-weight: 800; letter-spacing: -1px; margin: 0;">Admin Configuration</h1>
        <p style="color: #636e72; font-size: 0.9rem; margin: 5px 0 0 0;">Manage your security and notification preferences.</p>
    </div>

    {{-- System Success Alert Notification --}}
    @if(session('success'))
        <div style="background-color: #e8f5e9; border: 1px solid #a3e635; color: #2ecc71; padding: 14px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Global Validation Errors Fallback Alert --}}
    @if ($errors->any())
        <div style="background-color: #fff5f5; border: 1px solid #ff7675; color: #ff7675; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; font-size: 0.9rem; box-sizing: border-box;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ Route::has('admin.profile.update') ? route('admin.profile.update') : '#' }}" method="POST">
        @csrf
        @method('PUT') {{-- 🛠️ FIXED: Spoofs HTML form request type into a PUT request for your route matches --}}

        <div class="profile-card" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
            <div style="width: 80px; height: 80px; background-color: #3E2723; color: #FFFFFF; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin-bottom: 15px;">
                A
            </div>
            <h3 style="color: #3E2723; font-size: 1.4rem; font-weight: 800; margin: 0 0 5px 0;">System Administrator</h3>
            <p style="color: #7f8c8d; font-size: 0.9rem; margin: 0 0 25px 0;">admin@ipk.com</p>

            <div class="form-group" style="width: 100%; text-align: left;">
                <label for="username">Update Username</label>
                <input type="text" id="username" name="username" class="profile-input" 
                    style="border: 1px solid @error('username') #ff7675 @else #E0E0E0 @enderror;"
                    value="{{ old('username', 'Adminadmin') }}" required>
                @error('username')
                    <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="profile-card">
            <h4 style="color: #3E2723; font-weight: 800; font-size: 1.1rem; margin: 0 0 20px 0;">
                <i class="fa-solid fa-shield-halved" style="color: #8d6e63; margin-right: 8px;"></i> Security Settings
            </h4>

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="profile-input" 
                    style="border: 1px solid @error('current_password') #ff7675 @else #E0E0E0 @enderror;"
                    placeholder="••••••••••••">
                @error('current_password')
                    <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="profile-input" 
                        style="border: 1px solid @error('new_password') #ff7675 @else #E0E0E0 @enderror;"
                        placeholder="••••••••">
                    @error('new_password')
                        <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="profile-input" 
                        style="border: 1px solid @error('new_password_confirmation') #ff7675 @else #E0E0E0 @enderror;"
                        placeholder="••••••••">
                    @error('new_password_confirmation')
                        <small style="color: #ff7675; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h4 style="color: #3E2723; font-weight: 800; font-size: 1.1rem; margin: 0 0 20px 0;">
                <i class="fa-solid fa-bell" style="color: #8d6e63; margin-right: 8px;"></i> System Notifications
            </h4>

            <div class="toggle-row" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #F5F5F5;">
                <div>
                    <strong style="color: #3E2723; display: block; font-size: 0.95rem;">Payment Alerts</strong>
                    <span style="color: #7f8c8d; font-size: 0.85rem;">Receive emails when a tenant submits a payment.</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="alert_payments" class="sound-toggle" value="1" {{ (isset($prefs) && !($prefs['alert_payments'] ?? true)) ? '' : 'checked' }}>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <div>
                    <strong style="color: #3E2723; display: block; font-size: 0.95rem;">Maintenance Tickets</strong>
                    <span style="color: #7f8c8d; font-size: 0.85rem;">Receive emails for new facility issues.</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="alert_maintenance" class="sound-toggle" value="1" {{ (isset($prefs) && !($prefs['alert_maintenance'] ?? true)) ? '' : 'checked' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <button type="submit" class="submit-btn">
            <i class="fa-solid fa-floppy-disk"></i> Save All Configurations
=======
@section('content')

@if(session('success'))
    <div style="background-color: #efebe9; border: 1px solid #3E2723; color: #3E2723; padding: 12px 20px; border-radius: 10px; max-width: 600px; margin-bottom: 20px; font-weight: 700;">
        {{ session('success') }}
    </div>
@endif

<div style="margin-bottom: 30px;">
    <h1 style="color: #3E2723; font-weight: 800; margin: 0;">Admin Configuration</h1>
</div>

<div class="card" style="background: white; border-radius: 20px; padding: 40px; max-width: 600px; box-shadow: 0 10px 40px rgba(0,0,0,0.02);">
    
    <!-- Form wrapper added around your fields -->
    <form action="{{ route('admin.profile.update') }}" method="POST">
        @csrf
        
        <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 10px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #3E2723; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin-bottom: 15px;">
                A
            </div>
            <h3 style="color: #3E2723; font-weight: 800; margin: 0 0 5px 0; font-size: 1.3rem;">System Administrator</h3>
            <p style="color: #7f8c8d; font-size: 0.9rem; margin: 0;">cozyhabitat.admin@gmail.com</p>
        </div>

        <hr style="border: 0; border-top: 1px solid #f5f5f4; margin: 5px 0;">

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #3E2723; margin-bottom: 8px; text-transform: uppercase;">Update Username</label>
            <!-- Added name attribute -->
            <input type="text" name="username" value="Admin" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #3E2723; margin-bottom: 8px; text-transform: uppercase;">Change System Password</label>
            <!-- Changed type to "password" and added name attribute -->
            <input type="password" name="password" placeholder="••••••••••••" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; box-sizing: border-box;">
        </div>

        <!-- Button type changed to submit -->
        <button type="submit" style="width: 100%; background: #3E2723; color: #ffffff; border: none; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
        </button>
    </form>
</div>

<script>
    function playToggleSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const audioCtx = new AudioContext();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(600, audioCtx.currentTime); 
            oscillator.frequency.exponentialRampToValueAtTime(800, audioCtx.currentTime + 0.1);

            gainNode.gain.setValueAtTime(0.2, audioCtx.currentTime); 
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.1);
        } catch (e) {
            console.log("Audio contexts initializing blocked.");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.sound-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', playToggleSound);
        });
    });
</script>
@endsection