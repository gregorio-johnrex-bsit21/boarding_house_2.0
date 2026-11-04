<?php
namespace App\Http\Controllers;

use App\Models\{Bed, MaintenanceRequest, Payment, Room, Setting, User};
use App\Models\DeviceDeclaration;
use App\Models\DeviceCatalog;
use App\Models\UtilityBill;
use App\Http\Requests\ApproveDeviceDeclarationRequest;
use App\Http\Requests\DeleteDeviceCatalogRequest;
use App\Http\Requests\RejectDeviceDeclarationRequest;
use App\Http\Requests\StoreDeviceCatalogRequest;
use App\Http\Requests\StoreElectricityBillRequest;
use App\Http\Requests\UpdateDeviceCatalogRequest;
use App\Support\Billing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $deviceDeclarations = DeviceDeclaration::with(['user.bed.room', 'reviewer'])
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->get();
        $deviceCatalog = DeviceCatalog::query()
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%charging%'])
            ->withCount([
                'declarations as declaration_count',
                'declarations as active_declarations_count' => fn ($query) => $query->whereIn('device_declarations.status', ['pending', 'approved']),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $recentElectricityBills = UtilityBill::query()
            ->where('type', 'electricity')
            ->orderByDesc('period')
            ->limit(12)
            ->get();
        $recentElectricityBills->each(function (UtilityBill $bill): void {
            $shares = Billing::electricityShares($bill->period);
            $tenants = User::query()
                ->whereIn('id', array_keys($shares))
                ->with('bed.room')
                ->orderBy('id')
                ->get();
            $bill->setAttribute('tenant_shares', $tenants->map(fn (User $tenant) => [
                'tenant' => $tenant,
                'share' => $shares[$tenant->id],
            ]));
        });

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
            'deviceDeclarations' => $deviceDeclarations,
            'deviceCatalog' => $deviceCatalog,
            'recentElectricityBills' => $recentElectricityBills,
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

    public function storeDeviceCatalog(StoreDeviceCatalogRequest $request)
    {
        $data = $request->validated();
        $sortOrder = (int) DeviceCatalog::max('sort_order') + 1;
        DeviceCatalog::create([
            'name' => trim($data['name']),
            'note' => filled($data['note'] ?? null) ? trim($data['note']) : null,
            'available' => true,
            'reason' => null,
            'sort_order' => min($sortOrder, 65535),
            'total_units' => $data['total_units'] ?? null,
        ]);

        return $this->back('Device added to the catalog.', 'settings');
    }

    public function updateDeviceCatalog(UpdateDeviceCatalogRequest $request, DeviceCatalog $deviceCatalog)
    {
        abort_if(stripos($deviceCatalog->name, 'charging') !== false, 404);
        $data = $request->validated();
        $available = filter_var($data['available'], FILTER_VALIDATE_BOOLEAN);
        $deviceCatalog->update([
            'available' => $available,
            'reason' => $available || ! filled($data['reason'] ?? null) ? null : trim($data['reason']),
            'total_units' => array_key_exists('total_units', $data)
                ? $data['total_units']
                : $deviceCatalog->total_units,
        ]);

        return $this->back('Device availability updated.', 'settings');
    }

    public function destroyDeviceCatalog(DeleteDeviceCatalogRequest $request, DeviceCatalog $deviceCatalog)
    {
        abort_if(stripos($deviceCatalog->name, 'charging') !== false, 404);
        if (DB::table('device_declaration_catalog')->where('device_catalog_id', $deviceCatalog->id)->exists()) {
            return $this->back('This device cannot be deleted while declarations reference it.', 'settings')
                ->withErrors(['device_catalog' => 'This device is in use by a declaration.']);
        }

        $deviceCatalog->delete();

        return $this->back('Device removed from the catalog.', 'settings');
    }

    public function approveDeviceDeclaration(ApproveDeviceDeclarationRequest $request, DeviceDeclaration $deviceDeclaration)
    {
        DB::transaction(function () use ($request, $deviceDeclaration): void {
            $declaration = DeviceDeclaration::query()->lockForUpdate()->findOrFail($deviceDeclaration->id);
            abort_unless($declaration->status === 'pending', 409, 'This device declaration has already been reviewed.');
            $declaration->update([
                'status' => 'approved',
                'monthly_fee' => $request->validated('fee'),
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return $this->back('Device declaration approved.', 'settings');
    }

    public function rejectDeviceDeclaration(RejectDeviceDeclarationRequest $request, DeviceDeclaration $deviceDeclaration)
    {
        DB::transaction(function () use ($request, $deviceDeclaration): void {
            $declaration = DeviceDeclaration::query()->lockForUpdate()->findOrFail($deviceDeclaration->id);
            abort_unless($declaration->status === 'pending', 409, 'This device declaration has already been reviewed.');
            $declaration->update([
                'status' => 'rejected',
                'monthly_fee' => null,
                'rejection_reason' => $request->validated('reason'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return $this->back('Device declaration rejected.', 'settings');
    }

    public function storeElectricityBill(StoreElectricityBillRequest $request)
    {
        $data = $request->validated();
        $period = Carbon::createFromFormat('!Y-m', $data['period'])->startOfMonth();

        DB::transaction(function () use ($request, $data, $period): void {
            $bill = UtilityBill::query()
                ->whereDate('period', $period->toDateString())
                ->where('type', 'electricity')
                ->lockForUpdate()
                ->first();
            if ($bill) {
                $bill->update(['total_amount' => $data['total_amount']]);
                return;
            }

            UtilityBill::create([
                'period' => $period->toDateString(),
                'type' => 'electricity',
                'total_amount' => $data['total_amount'],
                'created_by' => $request->user()->id,
            ]);
        });

        return $this->back('Electricity bill saved.', 'settings');
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
        $timestamps = [];
        if ($data['status'] === 'in_progress' && ! $maintenanceRequest->in_progress_at) $timestamps['in_progress_at'] = now();
        if ($data['status'] === 'resolved') $timestamps['resolved_at'] = now();
        elseif ($maintenanceRequest->status === 'resolved') $timestamps['resolved_at'] = null;
        $maintenanceRequest->update($data + $timestamps);
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
        if (Billing::amountToCents($data['amount']) > Billing::amountToCents($c['remaining'])) {
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

    public function updatePaymentStatus(Request $r, Payment $payment)
    {
        $data = $r->validate(['status' => ['required', Rule::in(['verified', 'rejected'])]]);
        abort_unless($payment->status === 'pending', 409, 'This payment has already been reviewed.');

        $payment->update([
            'status' => $data['status'],
            'verified_by' => $data['status'] === 'verified' ? $r->user()->id : null,
            'verified_at' => $data['status'] === 'verified' ? now() : null,
        ]);

        return $this->back('Payment '.$data['status'].'.', 'payments');
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
