@extends('layouts.client')

@section('title', 'My Bills')

@section('content')
<h1 style="color: #3d2b1f; font-weight: 800; margin-bottom: 25px;">Payment History</h1>

<div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <table style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
        <thead>
            <tr style="text-align: left; color: #b2bec3; font-size: 0.8rem; text-transform: uppercase;">
                <th style="padding: 10px;">Billing Month</th>
                <th style="padding: 10px;">Due Date</th>
                <th style="padding: 10px;">Amount</th>
                <th style="padding: 10px;">Status</th>
            </tr>
        </thead>
        <tbody>
            {{-- This would be looped from your database later --}}
            <tr style="background: #f9f9f9;">
                <td style="padding: 15px; border-radius: 10px 0 0 10px; font-weight: 600;">October 2026</td>
                <td style="padding: 15px;">Oct 25, 2026</td>
                <td style="padding: 15px; font-weight: 700;">₱3,500.00</td>
                <td style="padding: 15px; border-radius: 0 10px 10px 0;">
                    <span style="padding: 5px 12px; border-radius: 20px; background: #fff5f5; color: #ff7675; font-size: 0.75rem; font-weight: 700; border: 1px solid currentColor;">UNPAID</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection