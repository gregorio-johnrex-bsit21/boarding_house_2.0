@extends('layouts.admin')

@section('content')
<<<<<<< HEAD
<style>
    /* Premium UI Enhancement Rules */
    .admin-grid-entrance {
        animation: fadeInSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .admin-card {
        background: #f5f4f2; /* Soft light grey instead of glaring white */
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        border: 1px solid rgba(74, 54, 41, 0.1);
        margin-bottom: 30px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .admin-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 45px rgba(74, 54, 41, 0.08);
    }
    .config-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
        margin-top: 15px;
    }
    .channel-box {
        background: rgba(255, 255, 255, 0.6);
        border-radius: 16px;
        padding: 20px;
        border: 1px solid rgba(74, 54, 41, 0.1);
    }
    .input-group {
        margin-bottom: 12px;
    }
    .input-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #4A3629;
        text-transform: uppercase;
        margin-bottom: 5px;
        letter-spacing: 0.3px;
        opacity: 0.8;
    }
    .input-group input, .input-group select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid rgba(74, 54, 41, 0.2);
        background: #ffffff; /* Keeping 30% pure white only for interactive inputs */
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 600;
        box-sizing: border-box;
        color: #4A3629;
        transition: all 0.2s;
    }
    .input-group input:focus, .input-group select:focus {
        outline: none;
        border-color: #4A3629;
        box-shadow: 0 0 0 3px rgba(74, 54, 41, 0.15);
    }
    .save-config-btn {
        background: #4A3629;
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .save-config-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .save-config-btn:disabled {
        background: rgba(74, 54, 41, 0.5);
        cursor: not-allowed;
        transform: none;
    }

    /* 🔔 Re-engineered High-Fidelity Alert Framework */
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
        background-color: #e8f5e9;
        border-left: 5px solid #2ed573;
        color: #1b5e20;
    }
    .portal-alert.error {
        background-color: #fef2f2;
        border-left: 5px solid #ff6b81;
        color: #7f1d1d;
    }

    @keyframes fadeInSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="admin-grid-entrance">
    {{-- Dynamic Silent Async Status Alert Container --}}
    <div id="admin-ajax-alert" class="portal-alert"></div>

    {{-- Fallback Session Alerts --}}
    @if(session('success'))
        <div style="background-color: #e8f5e9; border: 1px solid #2ed573; color: #1b5e20; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header Title Console Row --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="color: #4A3629; font-weight: 800; margin: 0; font-size: 1.75rem; letter-spacing: -0.5px;">Payment Transaction History</h1>
        <button onclick="togglePaymentModal(true)" style="background: #4A3629; color: white; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
            <i class="fa-solid fa-plus"></i> Record New Payment
        </button>
    </div>

    {{-- Dynamic Gateway Control Panel Component --}}
    <div class="admin-card">
        <div style="display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(74, 54, 41, 0.1); padding-bottom: 15px; margin-bottom: 15px;">
            <div style="background: rgba(74, 54, 41, 0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-gears" style="color: #4A3629; font-size: 1.15rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: #4A3629; font-weight: 800; font-size: 1.1rem; letter-spacing: -0.2px;">Payment Channel Configurations</h3>
                <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #4A3629; opacity: 0.7; font-weight: 500;">Update target numbers and display parameters mapped directly to the user portal.</p>
            </div>
        </div>

        <form id="payment-settings-form" action="{{ route('admin.payment-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="config-grid">
                {{-- GCash Settings Column Component --}}
                <div class="channel-box" style="border-left: 4px solid #0056b3;">
                    <h4 style="margin: 0 0 15px 0; color: #0056b3; font-weight: 800; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                        <i class="fa-solid fa-wallet"></i> GCash Gateway Parameters
                    </h4>
                    <div class="input-group">
                        <label>Account Phone Number</label>
                        <input type="text" name="gcash_number" value="{{ old('gcash_number', $settings->gcash_number ?? '0917-123-4567') }}" required>
                    </div>
                    <div class="input-group">
                        <label>Display Label (e.g., Manager Name)</label>
                        <input type="text" name="gcash_name" value="{{ old('gcash_name', $settings->gcash_name ?? 'IPK MANAGER') }}" required>
                    </div>
                </div>

                {{-- PayMaya Settings Column Component --}}
                <div class="channel-box" style="border-left: 4px solid #70a1ff;">
                    <h4 style="margin: 0 0 15px 0; color: #2f3542; font-weight: 800; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                        <i class="fa-solid fa-credit-card"></i> PayMaya Gateway Parameters
                    </h4>
                    <div class="input-group">
                        <label>Account Phone Number</label>
                        <input type="text" name="paymaya_number" value="{{ old('paymaya_number', $settings->paymaya_number ?? '0917-765-4321') }}" required>
                    </div>
                    <div class="input-group">
                        <label>Display Label (e.g., Manager Name)</label>
                        <input type="text" name="paymaya_name" value="{{ old('paymaya_name', $settings->paymaya_name ?? 'IPK MANAGER') }}" required>
                    </div>
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" id="config-submit-btn" class="save-config-btn">
                    <i class="fa-solid fa-floppy-disk"></i> <span>Save Portal Gateway Parameters</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Main Transaction History Matrix Ledger --}}
    <div class="admin-card" style="overflow-x: auto; padding: 25px;">
        
        {{-- Live Search Bar UI --}}
        <div style="margin-bottom: 20px; display: flex; gap: 10px;">
            <div style="flex: 1; position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #4A3629; opacity: 0.6;"></i>
                <input type="text" id="transactionSearch" placeholder="Search by Room Number or Reference..." style="width: 100%; padding: 14px 15px 14px 45px; border-radius: 12px; border: 1px solid rgba(74, 54, 41, 0.2); font-family: inherit; font-size: 0.95rem; box-sizing: border-box; font-weight: 600; color: #4A3629; background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.01); outline: none;" onfocus="this.style.borderColor='#4A3629'" onblur="this.style.borderColor='rgba(74, 54, 41, 0.2)'">
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(74, 54, 41, 0.15); color: #4A3629; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px;">
                    <th style="padding: 18px 12px; font-weight: 800;">Room</th>
                    <th style="padding: 18px 12px; font-weight: 800;">Reference #</th>
                    <th style="padding: 18px 12px; font-weight: 800;">Date</th>
                    <th style="padding: 18px 12px; font-weight: 800;">Amount</th>
                    <th style="padding: 18px 12px; font-weight: 800;">Receipt</th> 
                    <th style="padding: 18px 12px; text-align: right; font-weight: 800;">Status</th>
                </tr>
            </thead>
            <tbody id="paymentLedgerRows">
                @forelse($payments as $payment)
                <tr style="border-bottom: 1px solid rgba(74, 54, 41, 0.05); font-size: 0.95rem; color: #2d3436; transition: background 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.5)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 18px 12px; font-weight: 800; color: #4A3629;">{{ $payment->room_number }}</td>
                    
                    <td style="padding: 18px 12px; font-family: monospace; color: #4A3629; opacity: 0.8; font-weight: 700; letter-spacing: -0.2px;">
                        {{ $payment->reference_number ?? 'Cash Payment' }}
                    </td>
                    
                    <td style="padding: 18px 12px; color: #4A3629; opacity: 0.7; font-weight: 500;">{{ $payment->date }}</td>
                    <td style="padding: 18px 12px; font-weight: 800; color: #4A3629;">₱{{ number_format($payment->amount, 2) }}</td>
                    
                    <td style="padding: 18px 12px;">
                        @if($payment->receipt_path)
                            <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" style="color: #4A3629; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: rgba(74, 54, 41, 0.08); border-radius: 8px; font-size: 0.85rem; transition: all 0.2s;" onmouseover="this.style.background='rgba(74, 54, 41, 0.15)'" onmouseout="this.style.background='rgba(74, 54, 41, 0.08)'">
                                <i class="fa-solid fa-image" style="font-size: 0.85rem;"></i> View
                            </a>
                        @else
                            <span style="color: #4A3629; opacity: 0.4; font-style: italic; font-weight: 500; font-size: 0.85rem;">N/A</span>
                        @endif
                    </td>

                    <td style="padding: 18px 12px; text-align: right;">
                        @if(strtoupper($payment->status) == 'PENDING')
                            <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST" style="margin: 0; display: inline-block;">
                                @csrf
                                <button type="submit" style="background: #2ed573; color: white; border: none; padding: 7px 16px; border-radius: 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(46, 213, 115, 0.15); transition: 0.2s;" onmouseover="this.style.background='#26af5f'" onmouseout="this.style.background='#2ed573'">
                                    APPROVE
                                </button>
                            </form>
                        @else
                            <span style="padding: 6px 14px; border-radius: 30px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; display: inline-block;
                                background: {{ strtoupper($payment->status) == 'PAID' || strtoupper($payment->status) == 'APPROVED' ? '#e3fcef' : '#fff5f5' }}; 
                                color: {{ strtoupper($payment->status) == 'PAID' || strtoupper($payment->status) == 'APPROVED' ? '#2ed573' : '#ff6b81' }};">
                                {{ strtoupper($payment->status) }}
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 60px 40px; text-align: center; color: #4A3629; font-style: italic; opacity: 0.6;">
                        <i class="fa-solid fa-receipt" style="display: block; font-size: 2.5rem; margin-bottom: 12px; color: #4A3629; opacity: 0.2;"></i>
                        <span style="font-weight: 600; font-size: 0.95rem;">No ledger records found in the database.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Record Payment Modal Featuring Dynamic Channel Selector --}}
<div id="paymentRecordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(74, 54, 41, 0.7); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px);">
    <div style="background: #f5f4f2; border-radius: 20px; padding: 35px; width: 100%; max-width: 460px; box-shadow: 0 20px 50px rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.2); position: relative; margin: 20px; animation: fadeInSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #4A3629; font-weight: 800; margin: 0; font-size: 1.4rem; letter-spacing: -0.3px;">Record Offline Payment</h2>
            <button onclick="togglePaymentModal(false)" style="background: none; border: none; color: #4A3629; opacity: 0.6; font-size: 1.2rem; cursor: pointer; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.payments.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            
            <div class="input-group">
                <label>Target Room Name/No.</label>
                <input type="text" name="room_number" required placeholder="e.g. 204-A">
            </div>

            <div class="input-group">
                <label>Amount Deposited (₱)</label>
                <input type="number" step="0.01" name="amount" required placeholder="e.g. 3500.00">
            </div>

            <div class="input-group">
                <label>Payment Channel / Method</label>
                <select name="payment_channel" required>
                    <option value="Cash" selected>Cash Payment</option>
                    <option value="GCash">GCash Gateway</option>
                    <option value="PayMaya">PayMaya Gateway</option>
                </select>
            </div>

            <div class="input-group">
                <label>Payment Reference (Optional)</label>
                <input type="text" name="reference_number" placeholder="Leave empty for Cash entries">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="button" onclick="togglePaymentModal(false)" style="flex: 1; background: rgba(74, 54, 41, 0.1); color: #4A3629; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="flex: 1; background: #4A3629; color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Save Ledger Entry
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePaymentModal(show) {
        document.getElementById('paymentRecordModal').style.display = show ? 'flex' : 'none';
    }

    // 🎵 Web Audio API Payment Success Chime Engine
    function playPaymentSuccessSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const audioCtx = new AudioContext();
            const now = audioCtx.currentTime;
            
            // Note 1 (Crisp chime base)
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, now); // A5 note
            gain1.gain.setValueAtTime(0.15, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.15);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.15);
            
            // Note 2 (High satisfying ring close behind)
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1318.51, now + 0.08); // E6 note
            gain2.gain.setValueAtTime(0.2, now + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start(now + 0.08);
            osc2.stop(now + 0.45);
        } catch (e) {
            console.warn("Audio Context engine blocked by user interaction guidelines:", e);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        // 🔔 Play sound automatically if standard form redirect contains flash 'success' session data
        @if(session('success'))
            playPaymentSuccessSound();
        @endif

        // ==================================================
        // --- ASYNC PORTAL GATEWAY PARAMETERS SUBMISSION ---
        // ==================================================
        const settingsForm = document.getElementById('payment-settings-form');
        const submitBtn = document.getElementById('config-submit-btn');
        const ajaxAlert = document.getElementById('admin-ajax-alert');

        if (settingsForm) {
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault(); 

                if (submitBtn) {
                    submitBtn.disabled = true;
                    const btnSpan = submitBtn.querySelector('span');
                    if (btnSpan) btnSpan.textContent = 'Saving Parameters...';
                }

                let formData = new FormData(settingsForm);
                let csrfToken = settingsForm.querySelector('input[name="_token"]')?.value || '';

                fetch(settingsForm.action, {
                    method: 'POST', 
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    
                    if (!response.ok) {
                        let failureMessage = 'System error detected during parameters write.';
                        
                        if (contentType && contentType.includes('application/json')) {
                            const errorJson = await response.json();
                            if (errorJson.errors) {
                                let firstErrorKey = Object.keys(errorJson.errors)[0];
                                failureMessage = errorJson.errors[firstErrorKey][0];
                            } else {
                                failureMessage = errorJson.message || failureMessage;
                            }
                        } else {
                            const errorText = await response.text();
                            if (errorText.includes('Page Expired') || response.status === 419) {
                                failureMessage = 'Security Token Expired. Please reload your dashboard context.';
                            }
                        }
                        throw new Error(failureMessage);
                    }
                    return response.json();
                })
                .then(data => {
                    ajaxAlert.className = 'portal-alert';

                    if (data.status === 'success' || data.success) {
                        ajaxAlert.classList.add('success', 'show');
                        ajaxAlert.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${data.message || 'Portal parameters successfully integrated.'}</span>`;
                        
                        // 🎵 Trigger notification sound on safe AJAX response confirmation
                        playPaymentSuccessSound();
                    } else {
                        ajaxAlert.classList.add('error', 'show');
                        ajaxAlert.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span>${data.message || 'System error detected during parameters write.'}</span>`;
                    }

                    setTimeout(() => { ajaxAlert.classList.remove('show'); }, 5000);
                })
                .catch(error => {
                    console.error('AJAX error pipeline operational exception:', error);
                    ajaxAlert.className = 'portal-alert error show';
                    ajaxAlert.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span>${error.message}</span>`;
                    setTimeout(() => { ajaxAlert.classList.remove('show'); }, 6000);
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        const btnSpan = submitBtn.querySelector('span');
                        if (btnSpan) btnSpan.textContent = 'Save Portal Gateway Parameters';
                    }
                });
            });
        }

        // ==========================================
        // --- 10-SECOND LIVE SEARCH REFRESH LOOP ---
        // ==========================================
        setInterval(function() {
            let searchInput = document.getElementById('transactionSearch');
            let searchQuery = searchInput ? searchInput.value : '';
            
            let fetchUrl = "{{ route('admin.payments.data') }}";
            if (searchQuery.trim() !== '') {
                fetchUrl += `?search=${encodeURIComponent(searchQuery)}`;
            }

            fetch(fetchUrl)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('paymentLedgerRows');
                    if (!tableBody || !data.payments) return;

                    let rowsHtml = '';

                    if (data.payments.length === 0) {
                        rowsHtml = `
                            <tr>
                                <td colspan="6" style="padding: 60px 40px; text-align: center; color: #4A3629; font-style: italic; opacity: 0.6;">
                                    <i class="fa-solid fa-receipt" style="display: block; font-size: 2.5rem; margin-bottom: 12px; color: #4A3629; opacity: 0.2;"></i>
                                    <span style="font-weight: 600; font-size: 0.95rem;">No ledger records found matching "${searchQuery}".</span>
                                </td>
                            </tr>`;
                    } else {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                        data.payments.forEach(payment => {
                            const reference = payment.reference_number ? payment.reference_number : 'Cash Payment';
                            const amountFormatted = parseFloat(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            
                            let receiptContent = `<span style="color: #4A3629; opacity: 0.4; font-style: italic; font-size: 0.85rem; font-weight: 500;">N/A</span>`;
                            if (payment.receipt_path) {
                                receiptContent = `
                                    <a href="/storage/${payment.receipt_path}" target="_blank" style="color: #4A3629; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: rgba(74, 54, 41, 0.08); border-radius: 8px; font-size: 0.85rem;" onmouseover="this.style.background='rgba(74, 54, 41, 0.15)'" onmouseout="this.style.background='rgba(74, 54, 41, 0.08)'">
                                        <i class="fa-solid fa-image" style="font-size: 0.85rem;"></i> View
                                    </a>`;
                            }

                            let statusActionArea = '';
                            if (payment.status.toUpperCase() === 'PENDING') {
                                statusActionArea = `
                                    <form action="/admin/payments/approve/${payment.id}" method="POST" style="margin: 0; display: inline-block;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <button type="submit" style="background: #2ed573; color: white; border: none; padding: 7px 16px; border-radius: 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(46, 213, 115, 0.15);">
                                            APPROVE
                                        </button>
                                    </form>`;
                            } else {
                                const isPaid = payment.status.toUpperCase() === 'PAID' || payment.status.toUpperCase() === 'APPROVED';
                                const bgBadge = isPaid ? '#e3fcef' : '#fff5f5';
                                const textBadge = isPaid ? '#2ed573' : '#ff6b81';
                                
                                statusActionArea = `
                                    <span style="padding: 6px 14px; border-radius: 30px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; display: inline-block; background: ${bgBadge}; color: ${textBadge};">
                                        ${payment.status.toUpperCase()}
                                    </span>`;
                            }

                            rowsHtml += `
                            <tr style="border-bottom: 1px solid rgba(74, 54, 41, 0.05); font-size: 0.95rem; color: #2d3436; transition: background 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.5)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 18px 12px; font-weight: 800; color: #4A3629;">${payment.room_number}</td>
                                <td style="padding: 18px 12px; font-family: monospace; color: #4A3629; opacity: 0.8; font-weight: 700; letter-spacing: -0.2px;">${reference}</td>
                                <td style="padding: 18px 12px; color: #4A3629; opacity: 0.7; font-weight: 500;">${payment.date}</td>
                                <td style="padding: 18px 12px; font-weight: 800; color: #4A3629;">₱${amountFormatted}</td>
                                <td style="padding: 18px 12px;">${receiptContent}</td>
                                <td style="padding: 18px 12px; text-align: right;">${statusActionArea}</td>
                            </tr>`;
                        });
                    }

                    tableBody.innerHTML = rowsHtml;
                })
                .catch(error => console.error('Silent layout synchronization heartbeat delay:', error));
        }, 10000);
    });
=======
@if(session('success'))
    <div style="background-color: #e8f5e9; border: 1px solid #00b894; color: #00b894; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div style="background-color: #fff5f5; border: 1px solid #ff7675; color: #ff7675; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 style="color: var(--primary); font-weight: 800; margin: 0;">Payment Transaction History</h1>
    <button onclick="togglePaymentModal(true)" class="btn-primary" style="background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-plus"></i> Record New Payment
    </button>
</div>

<div class="card" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #f5f5f4; color: var(--secondary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 15px 12px;">Target Room</th>
                <th style="padding: 15px 12px;">Billing Period Date</th>
                <th style="padding: 15px 12px;">Amount Invoiced</th>
                <th style="padding: 15px 12px; text-align: right;">Collection Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($upcomingDues as $due)
            <tr style="border-bottom: 1px solid #f5f5f4; font-size: 0.95rem; color: #2d3436;">
                <td style="padding: 15px 12px; font-weight: 700; color: #2d2d2d;">{{ $due['room'] }}</td>
                <td style="padding: 15px 12px; color: #636e72;">{{ $due['date'] }}</td>
                <td style="padding: 15px 12px; font-weight: 700; color: #2d2d2d;">{{ $due['amount'] }}</td>
                <td style="padding: 15px 12px; text-align: right;">
                    <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.3px; display: inline-block;
                        @if($due['status'] == 'Paid' || $due['status'] == 'PAID')
                            background: #e3fcef; color: #00b894;
                        @@elseif($due['status'] == 'Pending' || $due['status'] == 'PENDING')
                            background: #fffbeb; color: #d97706;
                        @else
                            background: #fff5f5; color: #ff7675;
                        @endif">
                        {{ strtoupper($due['status']) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div id="recordPaymentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="background: white; border-radius: 20px; padding: 35px; width: 100%; max-width: 480px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.05); position: relative; margin: 20px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: var(--primary); font-weight: 800; margin: 0; font-size: 1.4rem;">Record New Payment</h2>
            <button onclick="togglePaymentModal(false)" style="background: none; border: none; color: #636e72; font-size: 1.2rem; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.payments.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Select Room</label>
                <input type="text" name="room_id" required placeholder="e.g. 101" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Amount (₱)</label>
                <input type="number" step="0.01" name="amount" required placeholder="e.g. 3500" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Billing Date</label>
                <input type="date" name="date" required value="{{ date('Y-m-d') }}" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Collection Status</label>
                <select name="status" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #dcdde1; font-family: inherit; font-size: 0.95rem; background-color: white;">
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="button" onclick="togglePaymentModal(false)" style="flex: 1; background: #f1f2f6; color: #2f3542; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="flex: 1; background: var(--primary); color: white; padding: 12px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">
                    Save Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePaymentModal(show) {
    const modal = document.getElementById('recordPaymentModal');
    if (show) {
        modal.style.display = 'flex';
    } else {
        modal.style.display = 'none';
    }
}
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
</script>
@endsection