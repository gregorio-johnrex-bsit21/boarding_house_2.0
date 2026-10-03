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

        // Occupied tenant roster filters
        $roomFilter = trim((string) $r->query('room', ''));
        $roomFilter = $roomFilter === '' ? null : $roomFilter;
        $q = strtolower(trim($r->query('q', '')));
        $tenants = $rows->filter(function ($x) use ($roomFilter, $q) {
            $u = $x['user'];
            $hay = strtolower($u->name.' '.$u->email.' '.($u->bed?->full_name ?? ''));
            return $u->bed_id !== null
                && ($roomFilter === null || (string) $u->bed->room->number === (string) $roomFilter)
                && ($q === '' || str_contains($hay, $q));
        })->values();
        $rooms = Room::whereHas('beds.tenant', fn ($query) => $query->where('role', 'tenant'))
            ->get()->sortBy(fn ($room) => (int) $room->number)->values();
        $paymentStatus = $r->query('payment_status', 'all');
        if (! in_array($paymentStatus, ['all', 'paid', 'pending', 'due'], true)) $paymentStatus = 'all';
        $residentRows = $rows->filter(fn ($x) => $x['user']->bed_id !== null)->values();
        $latestPayments = Payment::whereIn('user_id', $residentRows->pluck('user.id'))
            ->where('period', now()->startOfMonth()->toDateString())->where('status', '!=', 'rejected')
            ->latest()->get()->unique('user_id')->keyBy('user_id');
        $paymentResidents = $residentRows->map(fn ($x) => $x + ['latest_payment' => $latestPayments->get($x['user']->id)])
            ->filter(function ($x) use ($paymentStatus) {
                if ($paymentStatus === 'all') return true;
                if ($paymentStatus === 'paid') return $x['billing']['status'] === 'paid';
                if ($paymentStatus === 'pending') return $x['billing']['status'] === 'pending';
                return ! in_array($x['billing']['status'], ['paid', 'pending'], true);
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
            'rooms' => $rooms,
            'paymentResidents' => $paymentResidents,
            'paymentFilter' => $paymentStatus,
            'requests' => MaintenanceRequest::with('tenant', 'bed.room')->orderByRaw("status = 'resolved'")->latest()->get(),
            'settings' => [
                'gcash_number' => Setting::get('gcash_number'),
                'gcash_name' => Setting::get('gcash_name'),
                'gcash_qr' => Setting::get('gcash_qr'),
                'rent' => [
                    'default_monthly_rate' => Setting::get('rent.default_monthly_rate', 1500),
                    'top_bunk_rate' => Setting::get('rent.top_bunk_rate'),
                    'bottom_bunk_rate' => Setting::get('rent.bottom_bunk_rate'),
                    'prorate' => filter_var(Setting::get('rent.prorate', '0'), FILTER_VALIDATE_BOOLEAN),
                ],
                'due_dates' => [
                    'grace_period_days' => Setting::get('due.grace_period_days', 3),
                    'due_soon_days' => Setting::get('due.due_soon_days', 5),
                ],
                'device_surcharges' => Setting::getJson('device_surcharges', []),
                'utilities' => Setting::getJson('utilities', [
                    'electricity' => ['total' => '0.00', 'split_method' => 'equal'],
                    'wifi' => ['total' => '0.00', 'split_method' => 'equal'],
                    'water' => ['total' => '0.00', 'split_method' => 'equal'],
                ]),
            ],
            'roomFilter' => $roomFilter, 'q' => $r->query('q', ''),
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
        $data = $r->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'payment_method' => ['required', Rule::in(['cash', 'e_wallet'])],
            'gcash_reference' => 'nullable|required_if:payment_method,e_wallet|string|max:30|unique:payments,gcash_reference',
            'tenant_signature' => 'nullable|required_if:payment_method,cash|string|max:1000000',
        ]);
        if ((float) $data['amount'] > $c['remaining'] + 0.004) {
            return $this->back('Payment exceeds the remaining balance of ₱'.number_format($c['remaining'], 2).'.', 'payments')
                ->withErrors(['amount' => 'Enter an amount no greater than the remaining balance.'])
                ->withInput();
        }
        $signaturePath = null;
        if ($data['payment_method'] === 'cash') {
            if (! preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=]+)$/', $data['tenant_signature'], $matches)) {
                return $this->back('Ask the tenant to sign before recording this cash payment.', 'payments')
                    ->withErrors(['tenant_signature' => 'A valid tenant signature is required.']);
            }
            $signatureBytes = base64_decode($matches[1], true);
            if ($signatureBytes === false || strlen($signatureBytes) > 512000) {
                return $this->back('The tenant signature could not be saved. Please try again.', 'payments')
                    ->withErrors(['tenant_signature' => 'The signature image is invalid or too large.']);
            }
            $signaturePath = 'payment-signatures/'.\Illuminate\Support\Str::uuid().'.png';
            Storage::disk('public')->put($signaturePath, $signatureBytes);
        }

        Payment::create([
            'user_id' => $user->id, 'bed_id' => $user->bed_id, 'period' => $c['period'],
            'period_start' => $c['period']->copy()->startOfMonth(),
            'period_end' => $c['period']->copy()->startOfMonth()->addMonthNoOverflow(), 'amount' => $data['amount'],
            'payment_method' => $data['payment_method'], 'gcash_reference' => $data['gcash_reference'] ?? null,
            'tenant_signature' => $signaturePath,
            'status' => 'verified', 'verified_by' => $r->user()->id, 'verified_at' => now(),
        ]);
        return $this->back(ucwords(str_replace('_', '-', $data['payment_method'])).' payment recorded for '.$user->name.'.', 'payments');
    }

    public function updateSettings(Request $r)
    {
        $data = $r->validate([
            'rent.default_monthly_rate' => 'required|numeric|min:0|max:1000000',
            'rent.top_bunk_rate' => 'nullable|numeric|min:0|max:1000000',
            'rent.bottom_bunk_rate' => 'nullable|numeric|min:0|max:1000000',
            'rent.prorate' => 'nullable|boolean',
            'due_dates.grace_period_days' => 'required|integer|min:0|max:31',
            'due_dates.due_soon_days' => 'required|integer|min:0|max:31',
            'device_surcharges' => 'nullable|array|max:30',
            'device_surcharges.*.name' => 'required|string|max:60',
            'device_surcharges.*.fee' => 'required|numeric|min:0|max:100000',
            'utilities' => 'required|array',
            'utilities.electricity.total' => 'required|numeric|min:0|max:100000000',
            'utilities.electricity.split_method' => ['required', Rule::in(['equal', 'devices'])],
            'utilities.wifi.total' => 'required|numeric|min:0|max:100000000',
            'utilities.wifi.split_method' => ['required', Rule::in(['equal', 'devices'])],
            'utilities.water.total' => 'required|numeric|min:0|max:100000000',
            'utilities.water.split_method' => ['required', Rule::in(['equal', 'devices'])],
            'gcash_number' => 'nullable|string|max:20', 'gcash_name' => 'nullable|string|max:100',
            'gcash_qr' => 'nullable|image|max:4096',
        ]);
        Setting::put('rent.default_monthly_rate', $data['rent']['default_monthly_rate']);
        Setting::put('rent.top_bunk_rate', $data['rent']['top_bunk_rate'] ?? null);
        Setting::put('rent.bottom_bunk_rate', $data['rent']['bottom_bunk_rate'] ?? null);
        Setting::put('rent.prorate', ! empty($data['rent']['prorate']) ? '1' : '0');
        Setting::put('due.grace_period_days', $data['due_dates']['grace_period_days']);
        Setting::put('due.due_soon_days', $data['due_dates']['due_soon_days']);
        Setting::putJson('device_surcharges', array_values($data['device_surcharges'] ?? []));
        Setting::putJson('utilities', $data['utilities']);
        if (array_key_exists('gcash_number', $data)) Setting::put('gcash_number', $data['gcash_number']);
        if (array_key_exists('gcash_name', $data)) Setting::put('gcash_name', $data['gcash_name']);
        if ($r->hasFile('gcash_qr')) {
            if ($old = Setting::get('gcash_qr')) Storage::disk('public')->delete($old);
            Setting::put('gcash_qr', $r->file('gcash_qr')->store('settings', 'public'));
        }
        return $this->back('Settings saved.', 'settings');
    }

    private function back(string $msg, string $tab)
    {
        return redirect()->route('admin.dashboard')->with(['status' => $msg, 'tab' => $tab]);
    }
}
