<?php
namespace App\Http\Controllers;

use App\Models\{MaintenanceRequest, Payment, User};
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
        ]);
    }

    public function map(Request $r)
    {
        $user = $r->user()->load('bed.room');
        return view('tenant.map', [
            'bed' => $user->bed,
        ]);
    }

    public function billing(Request $r)
    {
        $user = $r->user()->load('bed.room');
        return view('tenant.billing', [
            'user' => $user,
            'payments' => $user->payments()->latest()->get(),
        ]);
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
