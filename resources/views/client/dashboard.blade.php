@extends('layouts.client')

@section('title', 'Resident Dashboard')

@section('content')
<style>
    /* CSS Root Variable Fallbacks matching your 60-30-10 rule */
    :root {
        --primary-brown: #3E2723;
        --secondary-brown: #5D4037;
        --accent-cream: #faf9f6;
    }

    /* Clean grid: Left Profile block, Right Overview block */
    .portal-inner-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 28px;
        align-items: start;
        width: 100%;
    }

    /* Premium Frosted Glass Container Styles matching your 60-30-10 palette */
    .glass-inner-card {
        background: rgba(255, 255, 255, 0.55); 
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 12px 40px rgba(61, 43, 31, 0.04);
    }

    .profile-display-box {
        text-align: center;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 30px 20px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 25px;
    }

    .avatar-circle-premium {
        width: 90px;
        height: 90px;
        background: var(--primary-brown);
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.4rem;
        font-weight: 800;
        margin: 0 auto 18px;
        box-shadow: 0 8px 20px rgba(61, 43, 31, 0.15);
    }

    .meta-details-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .meta-details-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid rgba(61, 43, 31, 0.08);
        font-size: 0.88rem;
    }

    .meta-details-item:last-child {
        border-bottom: none;
    }

    .meta-details-item .label-text {
        color: var(--secondary-brown);
        font-weight: 700;
    }

    .meta-details-item .value-text {
        color: var(--primary-brown);
        font-weight: 600;
        max-width: 180px;
        text-align: right;
        word-break: break-all;
    }

    /* Content Area Metric Displays */
    .dashboard-metric-box {
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 20px;
        padding: 26px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dashboard-metric-box:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 10px 25px rgba(61, 43, 31, 0.05);
    }

    .unit-pill-badge {
        background: var(--primary-brown); 
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .unit-pill-badge:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* Connected Admin Alert Card Styling */
    .admin-broadcast-alert {
        background: #fff5f5;
        border-left: 5px solid #e74c3c;
        padding: 18px 22px;
        border-radius: 14px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.05);
    }

    .action-badge {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        display: inline-block;
    }
    
    .badge-success-glow { background: rgba(39, 174, 96, 0.14); color: #27ae60; border: 1px solid rgba(39, 174, 96, 0.14); }
    .badge-pending-glow { background: rgba(230, 126, 34, 0.14); color: #d35400; border: 1px solid rgba(230, 126, 34, 0.14); }

    .premium-ledger-table {
        width: 100%; 
        border-collapse: collapse;
    }

    .premium-ledger-table th {
        padding: 16px 14px; 
        color: var(--secondary-brown); 
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        border-bottom: 2px solid rgba(61, 43, 31, 0.1);
        text-align: left;
    }

    .premium-ledger-table tr {
        transition: background-color 0.2s ease;
    }

    .premium-ledger-table tr:hover td {
        background: rgba(255, 255, 255, 0.4);
    }

    .premium-ledger-table td {
        padding: 18px 14px; 
        color: var(--primary-brown);
        font-size: 0.92rem;
        border-bottom: 1px solid rgba(61, 43, 31, 0.05);
    }

    /* -------------------------------------------------------------------------- */
    /* 🖨️ PRINT PROFILE CSS RULES (Prevents dashboard leakage seen in image_d4c4e0.png) */
    /* -------------------------------------------------------------------------- */
    #invoice-print-zone {
        display: none;
    }

    @media print {
        /* Wipe UI layout panels and components out of viewport */
        body * {
            visibility: hidden;
            background: none !important;
            box-shadow: none !important;
        }

        /* Mount invoice template parameters safely */
        #invoice-print-zone, #invoice-print-zone * {
            visibility: visible;
        }

        #invoice-print-zone {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .invoice-meta-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .invoice-table th {
            background-color: #f7f5f2 !important;
            color: #3E2723;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 12px 10px;
            border-bottom: 2px solid #3E2723;
            text-align: left;
        }

        .invoice-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
    }
</style>

<div class="portal-inner-grid" id="dashboard-refresh-container">
    
    <!-- Profile Panel -->
    <div class="glass-inner-card">
        <div class="profile-display-box">
            <div class="avatar-circle-premium">
                {{ strtoupper(substr($user->name ?? 'R', 0, 1)) }}
            </div>
            <h3 style="color: var(--primary-brown); font-weight: 800; margin: 0; font-size: 1.25rem;">{{ $user->name ?? 'Resident Tenant' }}</h3>
            <p style="color: var(--secondary-brown); font-size: 0.82rem; font-weight: 600; margin-top: 4px;">Verified Resident Account</p>
        </div>

        <h4 style="color: var(--primary-brown); font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.75px; margin-bottom: 15px; padding-left: 2px;">My System Details</h4>
        <ul class="meta-details-list">
            <li class="meta-details-item"><span class="label-text">Email:</span> <span class="value-text">{{ $user->email ?? 'N/A' }}</span></li>
            <li class="meta-details-item"><span class="label-text">Phone:</span> <span class="value-text">{{ $phoneNumber ?? 'N/A' }}</span></li>
            <li class="meta-details-item"><span class="label-text">Registered:</span> <span class="value-text">{{ $registrationDate ?? 'N/A' }}</span></li>
            <li class="meta-details-item"><span class="label-text">Emergency No:</span> <span class="value-text">{{ $emergencyNumber ?? 'N/A' }}</span></li>
        </ul>
    </div>

    <!-- Main Content Panel -->
    <div class="glass-inner-card">
        
        @if(($currentDues ?? 0) > 0)
            <div class="admin-broadcast-alert">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <i class="fa-solid fa-bell-exclamation" style="color: #e74c3c; font-size: 1.4rem;"></i>
                    <div>
                        <h4 style="margin: 0; color: #c0392b; font-weight: 800; font-size: 0.95rem;">Administrative Payment Reminder</h4>
                        <p style="margin: 4px 0 0 0; color: #7f8c8d; font-size: 0.85rem; font-weight: 500;">Your room billing statement is due. Please settle your account balance.</p>
                    </div>
                </div>
                <a href="{{ route('client.payment') }}" class="unit-pill-badge" style="background: #e74c3c; text-decoration: none; font-size: 0.75rem;">
                    <i class="fa-solid fa-receipt"></i> Pay Statement
                </a>
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 38px;">
            <div>
                <h1 style="color: var(--primary-brown); font-weight: 800; margin: 0; font-size: 2rem; letter-spacing: -0.5px;">My Overview</h1>
                <p style="color: var(--secondary-brown); opacity: 0.8; margin-top: 4px; font-size: 0.95rem; font-weight: 500;">Welcome back to IPK Boarding House Portal console.</p>
            </div>
            <div class="unit-pill-badge"><i class="fa-solid fa-hotel"></i> {{ $roomNumber ?? 'N/A' }}</div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <div class="dashboard-metric-box">
                <p style="font-size: 0.75rem; color: var(--secondary-brown); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Current Dues</p>
                <h2 id="current-dues-container" style="color: var(--primary-brown); font-size: 2rem; font-weight: 800; margin: 12px 0; letter-spacing: -0.5px;">₱{{ number_format($currentDues ?? 0, 2) }}</h2>
                <p id="days-due-container" style="font-size: 0.85rem; color: #d97706; font-weight: 700; display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-clock"></i> {{ $daysDueText ?? 'Due soon' }}</p>
            </div>

            <div class="dashboard-metric-box">
                <p style="font-size: 0.75rem; color: var(--secondary-brown); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Space Config</p>
                <h2 style="color: var(--primary-brown); font-size: 1.6rem; font-weight: 800; margin: 14px 0;">{{ $roomType ?? 'N/A' }}</h2>
                <p style="font-size: 0.85rem; color: #27ae60; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <a href="#" onclick="window.print(); return false;" style="color: #27ae60; font-weight: 700; text-decoration: underline;">Print Room Invoice</a>
                </p>
            </div>
        </div>

        <div class="dashboard-metric-box" style="padding: 28px;">
            <h3 style="color: var(--primary-brown); font-weight: 800; font-size: 1.15rem; margin-bottom: 22px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-clock-rotate-left" style="color: #d97706;"></i> Recent Account Actions
            </h3>
            <div style="overflow-x: auto;">
                <table class="premium-ledger-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Allocation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-actions-tbody">
                        @forelse($recentActions as $action)
                            <tr>
                                <td style="font-weight: 700;">
                                    @if(isset($action->payment_date) || isset($action->date))
                                        {{ \Carbon\Carbon::parse($action->payment_date ?? $action->date)->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td style="font-family: monospace; font-weight: 600; color: var(--secondary-brown);">{{ $action->reference_number ?? $action->reference_code ?? 'N/A' }}</td>
                                <td style="color: var(--secondary-brown);">{{ $action->description ?? 'Monthly Space Rent' }}</td>
                                <td>
                                    <span class="action-badge {{ in_array(strtolower($action->status ?? ''), ['paid', 'approved']) ? 'badge-success-glow' : 'badge-pending-glow' }}">
                                        {{ strtoupper($action->status ?? 'Pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--secondary-brown); padding: 24px; font-style: italic;">No recent actions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🖨️ INVOICE MARKUP DESIGN (Visible strictly inside print context layout) -->
<div id="invoice-print-zone">
    <div class="invoice-header">
        <div>
            <h1 style="color: #3E2723; margin: 0; font-size: 26px; font-weight: 800;">IPK BOARDING HOUSE</h1>
            <p style="margin: 4px 0 0 0; font-size: 12px; color: #5D4037; font-weight: 600;">Official Billing Statement & Account Ledger</p>
        </div>
        <div style="text-align: right;">
            <h2 style="margin: 0; font-size: 16px; color: #3E2723; letter-spacing: 0.5px;">STATEMENT OF ACCOUNT</h2>
            <p style="margin: 4px 0 0 0; font-size: 11px; font-family: monospace; color: #5D4037;">Generated: {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    <hr style="border: 0; border-top: 2px solid #3E2723; margin: 20px 0;">

    <div class="invoice-meta-grid">
        <div>
            <span style="font-size: 10px; font-weight: 800; color: #5D4037; letter-spacing: 0.5px;">TENANT DETAILS</span>
            <div style="font-weight: 800; font-size: 15px; margin-top: 4px; color: #3E2723;">{{ $user->name ?? 'N/A' }}</div>
            <div style="font-size: 12px; margin-top: 2px; color: #5D4037;">{{ $user->email ?? 'N/A' }}</div>
            <div style="font-size: 12px; color: #5D4037;">{{ $phoneNumber ?? 'N/A' }}</div>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 10px; font-weight: 800; color: #5D4037; letter-spacing: 0.5px;">ACCOMMODATION ALLOCATION</span>
            <div style="font-weight: 800; font-size: 15px; margin-top: 4px; color: #3E2723;">Room {{ $roomNumber ?? 'N/A' }}</div>
            <div style="font-size: 12px; margin-top: 2px; color: #5D4037;">Classification: {{ $roomType ?? 'Standard Space' }}</div>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Item / Allocation Breakdown</th>
                <th style="text-align: right;">Total Assessment</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-weight: 700; color: #3E2723;">Boarding Space Monthly Rental Fee</div>
                    <div style="font-size: 11px; color: #666; margin-top: 2px;">Standard monthly system balance protection charge cycle loop.</div>
                </td>
                <td style="text-align: right; font-weight: 700; color: #3E2723;">₱{{ number_format($currentDues ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-left: auto; width: 240px; margin-top: 40px;">
        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 13px;">
            <span style="font-weight: 600; color: #5D4037;">Subtotal Amount:</span>
            <span style="font-weight: 700; color: #3E2723;">₱{{ number_format($currentDues ?? 0, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 12px 0; font-size: 16px;">
            <span style="font-weight: 800; color: #3E2723;">Current Balance Dues:</span>
            <span style="font-weight: 800; color: #3E2723;">₱{{ number_format($currentDues ?? 0, 2) }}</span>
        </div>
        
        <div style="text-align: right; margin-top: 4px;">
            @if(($currentDues ?? 0) <= 0)
                <span style="background: #e3fcef !important; color: #27ae60 !important; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid rgba(39,174,96,0.2);">SETTLED / PAID</span>
            @else
                <span style="background: #fff5f5 !important; color: #e74c3c !important; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid rgba(231,76,60,0.2);">OUTSTANDING BALANCE</span>
            @endif
        </div>
    </div>

    <div style="margin-top: 100px; text-align: center; font-size: 11px; color: #888; border-top: 1px dashed #ccc; padding-top: 15px;">
        Thank you for choosing IPK Boarding House. If you notice structural or financial discrepancies within this invoice statement, kindly reach out to administration immediately.
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Run page background sync heartbeat every 10 seconds
        setInterval(function () {
            fetch(window.location.href)
                .then(response => {
                    if (!response.ok) throw new Error('Silent refresh channel disconnected');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const freshContent = doc.getElementById('dashboard-refresh-container');
                    const activeContent = document.getElementById('dashboard-refresh-container');
                    
                    if (freshContent && activeContent) {
                        activeContent.innerHTML = freshContent.innerHTML;
                    }
                    console.log('🔄 Connected parameters sync cycle absolute.');
                })
                .catch(error => console.warn('Heartbeat skipped link cycle:', error));
        }, 10000);
    });
</script>
@endsection