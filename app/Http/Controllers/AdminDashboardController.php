<?php
namespace App\Http\Controllers;

use App\Models\{Bed, MaintenanceRequest, Payment, Room, Setting, User};
use App\Support\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    public function index(Request $r)
    {
        $beds = Bed::with(['room', 'tenant'])->orderBy('room_id')->orderBy('bed_no')->orderBy('bunk')->get();

        $rows = User::where('role', 'tenant')->with('bed.room')->orderBy('name')->get()
            ->map(fn ($u) => ['user' => $u, 'billing' => Billing::cycle($u)]);
        $billingByUser = $rows->mapWithKeys(fn ($x) => [$x['user']->id => $x['billing']]);

        // Tenant list filters
        $filter = $r->query('filter');
        $q = strtolower(trim($r->query('q', '')));
        $tenants = $rows->filter(function ($x) use ($filter, $q) {
            $u = $x['user'];
            $ok = match ($filter) {
                'occupied' => $u->bed_id !== null,
                'due_soon', 'paid', 'overdue' => $x['billing']['status'] === $filter,
                default => true,
            };
            $hay = strtolower($u->name.' '.$u->email.' '.($u->bed?->full_name ?? ''));
            return $ok && ($q === '' || str_contains($hay, $q));
        })->values();

        // Payload for the slide-over + 3D map bridge (keyed by mesh_id)
        $bedData = $beds->mapWithKeys(fn ($b) => [$b->mesh_id => [
            'id' => $b->id, 'mesh_id' => $b->mesh_id, 'status' => $b->status, 'label' => $b->label,
            'room' => $b->room->number, 'rate' => $b->rate,
            'tenant' => $b->tenant ? [
                'id' => $b->tenant->id, 'name' => $b->tenant->name, 'phone' => $b->tenant->phone,
                'billing' => $billingByUser[$b->tenant->id]['status'] ?? 'unpaid',
                'due' => ($billingByUser[$b->tenant->id]['due'] ?? now())->format('M j'),
            ] : null,
        ]]);

        return view('admin.dashboard', [
            'stats' => [
                'total' => $beds->count(),
                'occupied' => $beds->where('status', 'occupied')->count(),
                'available' => $beds->where('status', 'available')->count(),
                'pendingPayments' => Payment::where('status', 'pending')->count(),
                'openIssues' => MaintenanceRequest::where('status', '!=', 'resolved')->count(),
            ],
            'bedData' => $bedData,
            'statuses' => $beds->pluck('status', 'mesh_id'),
            'unassigned' => User::where('role', 'tenant')->whereNull('bed_id')->orderBy('name')->get(['id', 'name']),
            'tenants' => $tenants,
            'payments' => Payment::with('user.bed.room')->orderByRaw("status = 'pending' desc")->latest()->limit(60)->get(),
            'requests' => MaintenanceRequest::with('tenant', 'bed.room')->orderByRaw("status = 'resolved'")->latest()->get(),
            'rooms' => Room::all()->sortBy(fn ($x) => (int) $x->number)->values(),
            'settings' => [
                'gcash_number' => Setting::get('gcash_number'),
                'gcash_name' => Setting::get('gcash_name'),
                'gcash_qr' => Setting::get('gcash_qr'),
            ],
            'filter' => $filter, 'q' => $r->query('q', ''),
        ]);
    }

    /* ---------- JSON (routes/api.php) ---------- */

    public function updateBedStatus(Request $r, Bed $bed)
    {
        $data = $r->validate(['status' => ['required', Rule::in(Bed::STATUSES)]]);
        $bed->update($data);
        return response()->json(['mesh_id' => $bed->mesh_id, 'status' => $bed->status]);
    }

    public function updateMaintenance(Request $r, MaintenanceRequest $maintenanceRequest)
    {
        $data = $r->validate(['status' => ['required', Rule::in(MaintenanceRequest::STATUSES)]]);
        $maintenanceRequest->update($data + ['resolved_at' => $data['status'] === 'resolved' ? now() : null]);
        return response()->json(['id' => $maintenanceRequest->id, 'status' => $maintenanceRequest->status]);
    }

    /* ---------- Form posts (routes/web.php) ---------- */

    public function storeTenant(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20', 'password' => 'required|string|min:8',
        ]);
        User::create($data + ['role' => 'tenant']);
        return $this->back('Tenant account created.', 'tenants');
    }

    public function assign(Request $r, Bed $bed)
    {
        abort_if($bed->tenant()->exists(), 422, 'Bed already has a tenant.');
        $data = $r->validate(['user_id' => ['required', Rule::exists('users', 'id')->where('role', 'tenant')->whereNull('bed_id')]]);
        User::whereKey($data['user_id'])->update([
            'bed_id' => $bed->id, 'move_in_date' => now(), 'due_day' => min(now()->day, 28),
        ]);
        $bed->update(['status' => 'occupied']);
        return $this->back('Tenant assigned to '.$bed->full_name.'.', 'map');
    }

    public function vacate(Bed $bed)
    {
        User::where('bed_id', $bed->id)->update(['bed_id' => null]);
        $bed->update(['status' => 'ready']); // "ready to be available" after move-out
        return $this->back($bed->full_name.' is now Ready to be available.', 'map');
    }

    public function recordCash(Request $r, User $user)
    {
        abort_unless($user->role === 'tenant', 404);
        $c = Billing::cycle($user);
        $amount = $r->validate(['amount' => 'nullable|numeric|min:1'])['amount'] ?? $c['amount'];
        Payment::create([
            'user_id' => $user->id, 'bed_id' => $user->bed_id, 'period' => $c['period'], 'amount' => $amount,
            'payment_method' => 'cash', 'status' => 'verified', 'verified_by' => $r->user()->id, 'verified_at' => now(),
        ]);
        return $this->back('Cash payment recorded for '.$user->name.'.', 'tenants');
    }

    public function verifyPayment(Request $r, Payment $payment)
    {
        $payment->update(['status' => 'verified', 'verified_by' => $r->user()->id, 'verified_at' => now()]);
        return $this->back('Payment verified.', 'payments');
    }

    public function rejectPayment(Request $r, Payment $payment)
    {
        $payment->update(['status' => 'rejected', 'note' => $r->input('note'), 'verified_by' => $r->user()->id, 'verified_at' => now()]);
        return $this->back('Payment rejected.', 'payments');
    }

    public function updateSettings(Request $r)
    {
        $data = $r->validate([
            'gcash_number' => 'nullable|string|max:20', 'gcash_name' => 'nullable|string|max:100',
            'gcash_qr' => 'nullable|image|max:4096', 'rates' => 'array', 'rates.*' => 'nullable|numeric|min:0',
        ]);
        Setting::put('gcash_number', $data['gcash_number'] ?? null);
        Setting::put('gcash_name', $data['gcash_name'] ?? null);
        if ($r->hasFile('gcash_qr')) {
            if ($old = Setting::get('gcash_qr')) Storage::disk('public')->delete($old);
            Setting::put('gcash_qr', $r->file('gcash_qr')->store('settings', 'public'));
        }
        foreach ($data['rates'] ?? [] as $roomId => $rate) {
            if ($rate !== null) Room::whereKey($roomId)->update(['monthly_rate' => $rate]);
        }
        return $this->back('Settings saved.', 'settings');
    }

    private function back(string $msg, string $tab)
    {
        return redirect()->route('admin.dashboard')->with(['status' => $msg, 'tab' => $tab]);
    }
}
