<?php
namespace App\Http\Controllers;

use App\Models\{MaintenanceRequest, Payment, Setting, User};
use App\Support\Billing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantDashboardController extends Controller
{
    public function home(Request $r)
    {
        $user = $r->user()->load('bed.room');
        $roommates = $user->bed
            ? User::where('role', 'tenant')->whereHas('bed', fn ($q) => $q->where('room_id', $user->bed->room_id))
                ->where('id', '!=', $user->id)->pluck('name')
            : collect();

        return view('tenant.home', [
            'user' => $user, 'bed' => $user->bed, 'billing' => Billing::cycle($user), 'roommates' => $roommates,
            'statuses' => \App\Models\Bed::pluck('status', 'mesh_id'),
        ]);
    }

    public function billing(Request $r)
    {
        $user = $r->user()->load('bed.room');
        return view('tenant.billing', [
            'user' => $user, 'billing' => Billing::cycle($user),
            'payments' => $user->payments()->latest()->get(),
            'gcash' => ['number' => Setting::get('gcash_number'), 'name' => Setting::get('gcash_name'), 'qr' => Setting::get('gcash_qr')],
        ]);
    }

    public function storePayment(Request $r)
    {
        $user = $r->user()->load('bed.room');
        abort_unless($user->bed, 422, 'You have no bed assigned yet.');
        $c = Billing::cycle($user);
        if ($c['status'] === 'paid') return back()->withErrors(['amount' => 'This month is already paid.']);

        $data = $r->validate([
            'amount' => 'required|numeric|min:1|max:100000',
            'gcash_reference' => 'required|string|max:30|unique:payments,gcash_reference',
            'receipt' => 'required|image|max:4096',
        ], ['gcash_reference.unique' => 'That reference number was already submitted.']);

        Payment::create([
            'user_id' => $user->id, 'bed_id' => $user->bed_id, 'period' => $c['period'], 'amount' => $data['amount'],
            'payment_method' => 'gcash', 'gcash_reference' => $data['gcash_reference'],
            'receipt_image' => $r->file('receipt')->store('receipts', 'public'), 'status' => 'pending',
        ]);
        return redirect()->route('tenant.billing')->with('status', 'Payment submitted. Your landlord will verify it soon.');
    }

    public function maintenance(Request $r)
    {
        return view('tenant.maintenance', ['requests' => $r->user()->maintenanceRequests()->latest()->get()]);
    }

    public function storeMaintenance(Request $r)
    {
        $user = $r->user();
        $data = $r->validate([
            'issue_category' => ['required', Rule::in(array_keys(MaintenanceRequest::CATEGORIES))],
            'urgency' => ['required', Rule::in(['low', 'normal', 'urgent'])],
            'description' => 'required|string|max:1000',
            'image' => 'nullable|image|max:6144',
        ]);
        MaintenanceRequest::create([
            'tenant_id' => $user->id, 'bed_id' => $user->bed_id, 'issue_category' => $data['issue_category'],
            'urgency' => $data['urgency'], 'description' => $data['description'],
            'image' => $r->hasFile('image') ? $r->file('image')->store('maintenance', 'public') : null,
        ]);
        return redirect()->route('tenant.maintenance')->with('status', 'Report sent. We will update you here.');
    }
}
