@extends('layouts.client')

@section('title', 'Billing History')

@section('content')
<style>
    :root {
        --ui-master-dark: #3E2723;      /* Your exact dark brown theme color */
        --ui-text-dark: #3E2723;        /* Matching text hierarchy */
        --ui-card-white: #ffffff;       /* Solid white container background */
        --ui-accent-tan: #e3dad3;       /* Light warm tan accent from active tabs */
        --ui-border-light: #f0eae5;     /* Subtle row divider lines */
        --ui-text-muted: #bfaea6;       /* Soft tan/grey for N/A placeholders */
    }

    /* 🔒 SIDEBAR COLOR GUARD: Forces the sidebar container element to stay #3E2723 */
    .sidebar, 
    aside, 
    [class*="sidebar"],
    .sidebar-wrapper,
    .sidebar-container {
        background-color: var(--ui-master-dark) !important;
    }

    /* ✨ Smooth Entrance Animation for the Page Title */
    .page-header-title {
        color: var(--ui-text-dark);
        margin-top: 0;
        font-size: 1.75rem;
        margin-bottom: 25px;
        font-weight: 700;
        letter-spacing: -0.5px;
        
        opacity: 0;
        transform: translateY(10px);
        animation: titleEntrance 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* ✨ Smooth Entrance Animation for the Ledger Card */
    .history-card {
        background: var(--ui-card-white);
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 4px 24px rgba(62, 39, 35, 0.02);
        border: none;
        max-width: 100%;
        
        opacity: 0;
        transform: translateY(15px);
        animation: cardEntrance 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        animation-delay: 0.05s;
    }

    @keyframes titleEntrance { to { opacity: 1; transform: translateY(0); } }
    @keyframes cardEntrance { to { opacity: 1; transform: translateY(0); } }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .history-table th {
        background-color: var(--ui-master-dark);
        color: #ffffff;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
    }

    /* Clean rounded table headers */
    .history-table th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .history-table th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
    
    .history-table td {
        padding: 18px 20px;
        border-bottom: 1px solid var(--ui-border-light);
        color: var(--ui-text-dark);
        font-weight: 500;
        font-size: 0.95rem;
        transition: background-color 0.2s ease;
    }

    /* Micro-interaction on row hovers */
    .history-table tr:hover td {
        background-color: #faf9f8;
    }
    
    /* 🌟 Capsule Status Badges */
    .status-badge {
        padding: 5px 16px;
        border-radius: 20px; 
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.5px;
    }

    /* Paid / Approved states */
    .status-badge.paid, 
    .status-badge.approved { 
        background: var(--ui-master-dark); 
        color: #ffffff; 
    }

    /* Pending state */
    .status-badge.pending { 
        background: var(--ui-accent-tan); 
        color: var(--ui-master-dark);
    }

    /* Overdue state */
    .status-badge.overdue { 
        background: #fdf0ee; 
        color: #c0392b; 
    }

    /* 📸 View Receipt Action Button Component */
    .receipt-link {
        color: var(--ui-text-dark);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        background-color: #ffffff;
        border: 1px solid var(--ui-accent-tan);
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .receipt-link:hover { 
        background-color: var(--ui-master-dark);
        color: #ffffff; 
        border-color: var(--ui-master-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(62, 39, 35, 0.08);
    }

    .text-muted { 
        color: var(--ui-text-muted); 
        font-weight: 500;
    }

    .no-records { 
        text-align: center; 
        padding: 60px 20px; 
        color: var(--ui-text-dark); 
        font-size: 1.05rem; 
    }

    /* Success Alert Notification Banner */
    .custom-success-alert {
        background: var(--ui-card-white);
        border-left: 5px solid var(--ui-master-dark); 
        color: var(--ui-text-dark); 
        padding: 16px 20px; 
        border-radius: 12px; 
        margin-bottom: 25px; 
        font-weight: 700; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        font-size: 0.95rem; 
        box-shadow: 0 4px 16px rgba(62, 39, 35, 0.02);
        animation: alertPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.1) forwards;
    }
    @keyframes alertPop { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
</style>

{{-- Success Notification Alert Banner Component --}}
@if(session('success'))
    <div class="custom-success-alert">
        <i class="fa-solid fa-circle-check" style="color: var(--ui-master-dark); font-size: 1.2rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Title sits cleanly outside on the cream background page canvas --}}
<h3 class="page-header-title">Complete Billing Ledger</h3>

<div class="history-card" id="payment-ledger-container">
    <table class="history-table">
        <thead>
            <tr>
                <th>Reference ID</th>
                <th>Room</th>
                <th>Amount Paid</th>
                <th>Date Submitted</th>
                <th>Receipt Proof</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->reference_number ?? '#TRX-' . $payment->id }}</td>
                    <td>{{ $payment->room_number }}</td>
                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->date }}</td>
                    <td>
                        @if($payment->receipt_path)
                            <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="receipt-link">
                                <i class="fas fa-image"></i> View Receipt
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ strtolower($payment->status) }}">
                            @if(strtolower($payment->status) == 'paid' || strtolower($payment->status) == 'approved')
                                <i class="fas fa-circle-check"></i> Paid
                            @elseif(strtolower($payment->status) == 'pending')
                                <i class="fas fa-clock"></i> Pending
                            @else
                                <i class="fas fa-circle-exclamation"></i> {{ $payment->status }}
                            @endif
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-records">
                        <i class="fas fa-folder-open" style="font-size: 2.5rem; display: block; margin-bottom: 12px; color: var(--ui-accent-tan);"></i>
                        No payment transactions recorded yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    setInterval(function() {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const freshContent = doc.getElementById('payment-ledger-container');
                const activeContent = document.getElementById('payment-ledger-container');
                
                if (freshContent && activeContent) {
                    activeContent.innerHTML = freshContent.innerHTML;
                }
            })
            .catch(err => console.warn('Silent sync skipped standard cycle:', err));
    }, 10000);
</script>
@endsection