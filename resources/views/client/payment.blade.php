@extends('layouts.client')

@section('title', 'Online Payment')

@section('content')
<style>
    :root {
        /* Strict 60-30-10 Color System Mapping */
        --primary-brown: #3E2723;
        --secondary-white: #ffffff;
        --light-grey-accent: #f5f5f4;
        
        --success-green: #2ecc71;
        --danger-red: #e74c3c;
    }

    .payment-layout { 
        max-width: 1000px; 
        margin: 40px auto; 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
        gap: 40px; 
        align-items: start; 
        animation: fadeInSlideUp 0.6s ease-out;
    }

    .glass-card { 
        background: var(--secondary-white); 
        padding: 40px; 
        border-radius: 24px; 
        box-shadow: 0 15px 35px rgba(62, 39, 35, 0.05); 
        border: 1px solid rgba(62, 39, 35, 0.08); 
    }
    
    .display-card { 
        background: var(--primary-brown); 
        color: var(--secondary-white); 
        padding: 25px; 
        border-radius: 20px; 
        margin-bottom: 20px; 
        box-shadow: 0 10px 20px rgba(62, 39, 35, 0.15); 
        position: relative;
        overflow: hidden;
    }

    .display-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
    }

    .card-title { 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        opacity: 0.75; 
        margin-bottom: 8px; 
        font-weight: 700;
    }

    .card-number { 
        font-size: 1.6rem; 
        font-weight: 800; 
        font-family: 'Courier New', Courier, monospace; 
        letter-spacing: 1.5px; 
    }

    .channel-grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 15px; 
        margin-bottom: 25px; 
    }

    .radio-card { 
        padding: 20px; 
        border: 2px solid var(--light-grey-accent); 
        border-radius: 16px; 
        text-align: center; 
        cursor: pointer; 
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
        background: var(--secondary-white);
        display: block;
    }

    .radio-card strong {
        color: #555;
        transition: color 0.25s;
    }

    /* Active Selection utilizing the 10% Accents */
    .radio-card.active { 
        border-color: var(--primary-brown); 
        background: var(--light-grey-accent); 
        transform: scale(1.02); 
    }

    .radio-card.active strong {
        color: var(--primary-brown);
    }

    .radio-card input { 
        display: none; 
    }

    input { 
        width: 100%; 
        padding: 16px; 
        background: var(--secondary-white); 
        border: 2px solid var(--light-grey-accent); 
        border-radius: 12px; 
        font-size: 1rem; 
        box-sizing: border-box; 
        color: #2d2d2d;
        font-family: inherit;
        transition: all 0.3s;
    }
    
    input:focus { 
        outline: none; 
        border-color: var(--primary-brown); 
        box-shadow: 0 0 0 4px rgba(62, 39, 35, 0.08);
    }
    
    .submit-btn { 
        width: 100%; 
        padding: 18px; 
        background: var(--primary-brown); 
        color: var(--secondary-white); 
        border: none; 
        border-radius: 12px; 
        font-weight: 700; 
        font-size: 1rem;
        cursor: pointer; 
        transition: all 0.2s; 
    }

    .submit-btn:hover { 
        background: #271815; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(62, 39, 35, 0.2);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .upload-container { 
        padding: 20px; 
        border: 2px dashed rgba(62, 39, 35, 0.25); 
        border-radius: 12px; 
        cursor: pointer; 
        text-align: center; 
        color: #78716c; 
        transition: all 0.25s; 
        background: var(--light-grey-accent); 
    }

    .upload-container:hover { 
        border-color: var(--primary-brown); 
        color: var(--primary-brown); 
        background: #ecebe9;
    }

    .alert { 
        padding: 16px 20px; 
        border-radius: 12px; 
        margin-bottom: 25px; 
        font-size: 0.9rem; 
        font-weight: 600; 
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .alert-success { 
        background-color: #e8f5e9; 
        color: #2e7d32; 
        border: 1px solid #c8e6c9; 
    }
    
    .alert-danger { 
        background-color: #ffebee; 
        color: #c62828; 
        border: 1px solid #ffcdd2; 
    }
    
    .alert ul { 
        margin: 4px 0 0 0; 
        padding-left: 18px; 
    }

    @keyframes fadeInSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="payment-layout">
    {{-- Left Panel: Dynamic Configuration Channels --}}
    <div>
        <h2 style="color: var(--primary-brown); font-weight: 800; margin-top: 0; margin-bottom: 0.5rem; letter-spacing: -0.5px;">Payment Channels</h2>
        <p style="color: #666; margin-bottom: 30px; font-size: 0.95rem; line-height: 1.5;">Please settle your balance using the official channels specified below, then document your confirmation parameters.</p>
        
        <div class="display-card">
            <div class="card-title">
                <i class="fa-solid fa-wallet" style="margin-right: 6px;"></i> 
                GCash ({{ $settings?->gcash_name ?? 'IPK MANAGER' }})
            </div>
            <div class="card-number" id="card-gcash-number">{{ $settings?->gcash_number ?? '0917-123-4567' }}</div>
        </div>

        <div class="display-card">
            <div class="card-title">
                <i class="fa-solid fa-money-bill-wave" style="margin-right: 6px;"></i> 
                PayMaya ({{ $settings?->paymaya_name ?? 'IPK MANAGER' }})
            </div>
            <div class="card-number" id="card-paymaya-number">{{ $settings?->paymaya_number ?? '0917-765-4321' }}</div>
        </div>
    </div>

    {{-- Right Panel: Proof of Remittance Form Box --}}
    <div class="glass-card">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check" style="margin-top: 3px;"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation" style="margin-top: 3px;"></i>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Submission Validation Error:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('client.payment.submit') }}" method="POST" enctype="multipart/form-data" id="remittance-payment-form">
            @csrf
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; color:#78716c; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Amount Formulated (PHP)</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', $currentDues ?? '') }}" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; color:#78716c; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Channel Executed</label>
                <div class="channel-grid">
                    <label class="radio-card {{ old('payment_method', 'gcash') == 'gcash' ? 'active' : '' }}" id="card-gcash">
                        <input type="radio" name="payment_method" value="gcash" {{ old('payment_method', 'gcash') == 'gcash' ? 'checked' : '' }} onchange="selectMethod('gcash')">
                        <strong>GCash</strong>
                    </label>
                    <label class="radio-card {{ old('payment_method') == 'paymaya' ? 'active' : '' }}" id="card-paymaya">
                        <input type="radio" name="payment_method" value="paymaya" {{ old('payment_method') == 'paymaya' ? 'checked' : '' }} onchange="selectMethod('paymaya')">
                        <strong>PayMaya</strong>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; color:#78716c; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Transaction Reference Number</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g., 50123456789" required>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display:block; color:#78716c; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Upload Receipt Image</label>
                <div class="upload-container" onclick="document.getElementById('receipt-input').click()">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.4rem; display: block; margin-bottom: 6px;"></i>
                    <span id="file-name" style="font-size: 0.9rem; font-weight: 500;">Click to select receipt snapshot</span>
                    <input type="file" name="receipt" id="receipt-input" accept="image/*" style="display:none" onchange="updateFileName(this)" required>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="payment-submit-trigger">
                <i class="fa-solid fa-receipt" style="margin-right: 6px;"></i> Submit for Approval
            </button>
        </form>
    </div>
</div>

<script>
    function selectMethod(method) {
        document.querySelectorAll('.radio-card').forEach(el => el.classList.remove('active'));
        const targetCard = document.getElementById('card-' + method);
        if (targetCard) {
            targetCard.classList.add('active');
        }
    }

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const containerDisplay = document.getElementById('file-name');
            
            containerDisplay.innerText = fileName;
            containerDisplay.style.color = 'var(--primary-brown)';
            containerDisplay.style.fontWeight = '700';
        }
    }

    document.getElementById('remittance-payment-form').addEventListener('submit', function() {
        const targetBtn = document.getElementById('payment-submit-trigger');
        targetBtn.disabled = true;
        targetBtn.style.opacity = '0.8';
        targetBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Processing Statement...`;
    });

    // --- 10-Second Silent Refresh Logic ---
    let userHasModifiedAmount = false;
    const amountInput = document.querySelector('input[name="amount"]');

    if (amountInput) {
        amountInput.addEventListener('input', () => {
            userHasModifiedAmount = true;
        });
    }

    function silentRefreshData() {
        // Adjust this URL to match the exact route name you set up in web.php
        fetch('{{ route("client.payment.latest") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            // Update amount quietly, only if they haven't typed over it
            if (!userHasModifiedAmount && amountInput && data.currentDues !== undefined) {
                amountInput.value = data.currentDues;
            }
            
            // Update channel numbers dynamically
            const gcashEl = document.getElementById('card-gcash-number');
            const paymayaEl = document.getElementById('card-paymaya-number');
            
            if(gcashEl && data.gcash_number) gcashEl.innerText = data.gcash_number;
            if(paymayaEl && data.paymaya_number) paymayaEl.innerText = data.paymaya_number;
        })
        .catch(error => {
            console.error('Silent background update paused:', error);
        });
    }

    // Trigger the silent refresh every 10 seconds (10000 milliseconds)
    setInterval(silentRefreshData, 10000);
</script>
@endsection