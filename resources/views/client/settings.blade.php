@extends('layouts.client')

@section('title', 'Portal Settings')

@section('content')
<style>
    :root { 
        /* Strict 60-30-10 Color System Mapping */
        --primary-brown: #3E2723;
        --secondary-white: #ffffff;
        --light-grey-accent: #f5f5f4;
        --card-bg: rgba(255, 255, 255, 0.85); 
    }
    
    /* ✨ Smooth Dashboard Grid Entrance Animation */
    .settings-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
        gap: 2rem; 
        animation: fadeInSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .settings-card { 
        background: var(--secondary-white); 
        backdrop-filter: blur(20px);
        padding: 2.5rem; 
        border-radius: 24px; 
        border: 1px solid rgba(62, 39, 35, 0.06);
        box-shadow: 0 10px 30px rgba(62, 39, 35, 0.02);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .settings-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 20px 40px rgba(62, 39, 35, 0.06); 
    }

    .profile-meta-row { 
        display: flex; 
        justify-content: space-between; 
        padding: 1.1rem 0; 
        border-bottom: 1px solid rgba(62, 39, 35, 0.06);
        transition: all 0.3s ease;
    }
    
    .profile-meta-row:hover { 
        background: var(--light-grey-accent); 
        padding-left: 12px; 
        padding-right: 12px;
        border-radius: 10px;
    }

    input, select, textarea { 
        width: 100%; 
        padding: 14px; 
        border-radius: 12px; 
        border: 1px solid rgba(62, 39, 35, 0.12); 
        background: var(--secondary-white);
        font-family: inherit;
        color: #2d2d2d;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }
    
    input:focus, select:focus, textarea:focus { 
        outline: none; 
        border-color: var(--primary-brown); 
        box-shadow: 0 0 0 4px rgba(62, 39, 35, 0.08); 
    }

    /* 🚀 Premium Interaction Submit Button */
    .submit-btn { 
        background: var(--primary-brown); 
        color: var(--secondary-white); 
        padding: 16px; 
        border: none; 
        border-radius: 12px;
        font-weight: 700; 
        font-size: 0.95rem;
        cursor: pointer; 
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.25s ease;
    }
    
    .submit-btn:hover {
        background: #271815;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(62, 39, 35, 0.15);
    }
    
    .submit-btn:disabled {
        background: #a89e9b;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* 🔔 Redesigned High-Fidelity Alert Framework */
    .portal-alert {
        display: none;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        border-radius: 16px;
        margin-bottom: 25px;
        font-weight: 600;
        font-size: 0.95rem;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02);
        opacity: 0;
        transform: translateY(-12px);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
    }

    .portal-alert.show {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    .portal-alert.success {
        background-color: #f0fdf4;
        border-left: 5px solid #22c55e;
        color: #14532d;
    }

    .portal-alert.error {
        background-color: #fef2f2;
        border-left: 5px solid #ef4444;
        color: #7f1d1d;
    }

    @keyframes fadeInSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

{{-- Re-engineered Dynamic Status Notification Box --}}
<div id="portal-ajax-alert" class="portal-alert"></div>

<div class="settings-grid">
    {{-- Dynamic Resident Metadata Component --}}
    <div class="settings-card">
        <h3 style="color: var(--primary-brown); font-weight: 800; margin-top: 0; margin-bottom: 1.5rem; letter-spacing: -0.5px;">
            <i class="fa-solid fa-id-card" style="margin-right: 8px; color: #8d6e63;"></i> Resident Metadata
        </h3>
        
        @foreach($metadata as $label => $val)
            <div class="profile-meta-row">
                <span style="color: #78716c; font-weight: 500;">{{ $label }}</span>
                <span style="font-weight: 700; color: #2d2d2d;">{{ $val }}</span>
            </div>
        @endforeach
    </div>

    {{-- Maintenance Help Desk Component --}}
    <div class="settings-card">
        <h3 style="color: var(--primary-brown); font-weight: 800; margin-top: 0; margin-bottom: 0.5rem; letter-spacing: -0.5px;">
            <i class="fa-solid fa-headset" style="margin-right: 8px; color: #8d6e63;"></i> IPK Resident Help Desk
        </h3>
        <p style="margin-bottom: 1.5rem; color: #636e72; font-size: 0.9rem; line-height: 1.4;">
            Encountered an issue with your accommodation? Submit a maintenance ticket directly to system management.
        </p>
        
        <form id="helpdesk-ticket-form" action="{{ route('client.ticket.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #8c8c8c; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Issue Category</label>
                <select name="category" required>
                    <option value="Plumbing & Water Facility">Plumbing & Water Facility</option>
                    <option value="Electrical Grid">Electrical Grid</option>
                    <option value="Billing Dispute">Billing Dispute</option>
                    <option value="Structural & Structural Repair">Structural & Structural Repair</option>
                </select>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #8c8c8c; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Problem Description</label>
                <textarea name="description" rows="4" placeholder="Please explain the scenario or issue clearly so management can deploy structural fixes..." required></textarea>
            </div>
            <button type="submit" id="helpdesk-submit-btn" class="submit-btn" style="width: 100%;">
                <i class="fa-solid fa-paper-plane"></i> <span>Submit Support Request</span>
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ticketForm = document.getElementById('helpdesk-ticket-form');
        const alertBox = document.getElementById('portal-ajax-alert');
        const submitBtn = document.getElementById('helpdesk-submit-btn');

        if (ticketForm) {
            ticketForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Lock system controls and trigger premium transition spinner
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Dispatching Ticket...</span>`;

                // Wipe existing alert states cleanly
                alertBox.className = 'portal-alert';
                alertBox.style.display = 'none';

                const formData = new FormData(ticketForm);

                fetch(ticketForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' // Forces Laravel to send clean error messages back
                    }
                })
                .then(async response => {
                    const isJson = response.headers.get('content-type')?.includes('application/json');
                    const data = isJson ? await response.json() : null;

                    if (!response.ok) {
                        // Extract Laravel standard message array if present
                        const errorMessage = data?.message || 'Failed to process request on backend terminal context.';
                        throw new Error(errorMessage);
                    }
                    return data;
                })
                .then(data => {
                    // 🌟 SUCCESS STATE: Render pristine green validation banner
                    alertBox.innerHTML = `<i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i> <span>Ticket logged successfully! Administration has been updated.</span>`;
                    alertBox.style.display = 'flex';
                    
                    // Trigger entry frame cascade
                    setTimeout(() => alertBox.classList.add('show', 'success'), 10);
                    
                    ticketForm.reset();
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(error => {
                    // 🚨 ERROR STATE: Render premium red error block with real feedback trace
                    alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="font-size: 1.25rem;"></i> <span>${error.message}</span>`;
                    alertBox.style.display = 'flex';
                    
                    setTimeout(() => alertBox.classList.add('show', 'error'), 10);
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .finally(() => {
                    // Restore functional buttons
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> <span>Submit Support Request</span>`;
                    
                    // Auto-hide message banner context after 7 seconds
                    setTimeout(() => {
                        alertBox.classList.remove('show');
                        setTimeout(() => { if(!alertBox.classList.contains('show')) alertBox.style.display = 'none'; }, 400);
                    }, 7000);
                });
            });
        }
    });
</script>
@endsection