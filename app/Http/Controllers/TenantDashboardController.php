<?php
namespace App\Http\Controllers;

use App\Models\{MaintenanceRequest, Payment, User};
use App\Models\DeviceDeclaration;
use App\Models\DeviceCatalog;
use App\Models\DeviceDeclarationCatalog;
use App\Models\UtilityBill;
use App\Http\Requests\StoreDeviceDeclarationRequest;
use App\Support\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TenantDashboardController extends Controller
{
    public function home(Request $r)
    {
        $user = $r->user()->load('bed.room');
        $billing = Billing::cycle($user);
        $periodPayments = $user->payments()
            ->whereDate('period', $billing['period']->toDateString())
            ->latest()
            ->get();
        $periodRepairs = $user->maintenanceRequests()
            ->where(function ($query) use ($billing) {
                $query->whereBetween('created_at', [
                    $billing['period']->copy()->startOfMonth(),
                    $billing['period']->copy()->endOfMonth(),
                ])->orWhere('status', '!=', 'resolved');
            })
            ->latest()
            ->get();
        $openRepairCount = $user->maintenanceRequests()->where('status', '!=', 'resolved')->count();
        $cycleDue = $billing['due']->copy();
        if ($billing['remaining'] <= 0 && $cycleDue->format('Y-m') !== $billing['period']->format('Y-m')) {
            $cycleDue->subMonthNoOverflow();
        }
        $graceDays = max(0, (int) \App\Models\Setting::get('due.grace_period_days', 3));
        $graceEnd = $cycleDue->copy()->addDays($graceDays);
        $roommates = $user->bed
            ? User::where('role', 'tenant')->whereHas('bed', fn ($q) => $q->where('room_id', $user->bed->room_id))
                ->where('id', '!=', $user->id)->pluck('name')
            : collect();

        return view('tenant.home', [
            'user' => $user, 'bed' => $user->bed, 'billing' => $billing, 'roommates' => $roommates,
            'lastPayment' => $user->payments()->latest()->first(),
            'latestRepair' => $user->maintenanceRequests()->latest()->first(),
            'periodPayments' => $periodPayments,
            'periodRepairs' => $periodRepairs,
            'openRepairCount' => $openRepairCount,
            'graceEnd' => $graceEnd,
        ]);
    }

    public function map(Request $r)
    {
        $user = $r->user()->load('bed.room.beds.tenant');
        $roommates = $user->bed
            ? User::where('role', 'tenant')->with('bed.room')->whereHas('bed', fn ($q) => $q->where('room_id', $user->bed->room_id))
                ->where('id', '!=', $user->id)->orderBy('name')->get()
            : collect();
        return view('tenant.map', [
            'user' => $user,
            'bed' => $user->bed,
            'roommates' => $roommates,
        ]);
    }

    public function wifiDevices(Request $r)
    {
        $user = $r->user()->load('bed.room');
        $billing = Billing::cycle($user);
        $declarations = $user->reviewedDeviceDeclarations()
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%charging%'])
            ->with('catalogDevice')
            ->latest()
            ->get();
        $electricityBill = UtilityBill::query()
            ->whereDate('period', $billing['period']->toDateString())
            ->where('type', 'electricity')
            ->first();
        $deviceCatalog = DeviceCatalog::query()
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%charging%'])
            ->withCount(['declarations as active_declarations_count' => fn ($query) => $query->whereIn('device_declarations.status', ['pending', 'approved'])])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('tenant.wifi-devices', [
            'bed' => $user->bed,
            'user' => $user,
            'billing' => $billing,
            'electricityBill' => $electricityBill,
            'deviceDeclarations' => $declarations,
            'deviceCatalog' => $deviceCatalog,
        ]);
    }

    public function submitDeviceDeclaration(StoreDeviceDeclarationRequest $request)
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data): void {
            $catalogDevice = null;
            if (! empty($data['catalog_device_id'])) {
                $catalogDevice = DeviceCatalog::query()
                    ->lockForUpdate()
                    ->findOrFail($data['catalog_device_id']);
                $unitsLeft = $catalogDevice->unitsLeft();
                if (
                    stripos($catalogDevice->name, 'charging') !== false
                    || ! $catalogDevice->available
                    || ($unitsLeft !== null && $unitsLeft === 0)
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'catalog_device_id' => 'No units left for this device',
                    ]);
                }
            }

            $declaration = $request->user()->reviewedDeviceDeclarations()->create([
                'name' => $catalogDevice?->name ?? trim($data['name']),
                'note' => filled($data['note'] ?? null) ? trim($data['note']) : null,
                'status' => 'pending',
            ]);

            if ($catalogDevice) {
                DeviceDeclarationCatalog::create([
                    'device_declaration_id' => $declaration->id,
                    'device_catalog_id' => $catalogDevice->id,
                ]);
            }
        });

        return redirect()->route('tenant.wifi-devices')->with('status', 'Device declaration sent for landlord review.');
    }

    public function cancelDeviceDeclaration(Request $request, int $deviceDeclaration)
    {
        $request->user()->reviewedDeviceDeclarations()
            ->where('status', 'pending')
            ->findOrFail($deviceDeclaration)
            ->delete();

        return redirect()->route('tenant.wifi-devices')->with('status', 'Pending device declaration cancelled.');
    }

    public function storeDeviceDeclaration(Request $r)
    {
        $user = $r->user();
        abort_unless($user->bed_id, 422, 'A bed assignment is required before declaring a device.');

        $data = $r->validate([
            'name' => 'required|string|max:80',
            'notes' => 'nullable|string|max:500',
        ]);

        $user->deviceDeclarations()->create([
            'bed_id' => $user->bed_id,
            'name' => trim($data['name']),
            'notes' => isset($data['notes']) ? trim($data['notes']) : null,
            'status' => 'pending',
        ]);

        return redirect()->route('tenant.wifi-devices')->with('status', 'Device declaration sent for landlord review.');
    }

    public function destroyDeviceDeclaration(Request $r, int $deviceDeclaration)
    {
        $r->user()->deviceDeclarations()
            ->where('status', 'pending')
            ->findOrFail($deviceDeclaration)
            ->delete();

        return redirect()->route('tenant.wifi-devices')->with('status', 'Pending device declaration cancelled.');
    }

    public function updatePreferences(Request $r)
    {
        $data = $r->validate([
            'notifications' => ['required', 'array:rent,repairs,notices'],
            'notifications.rent' => ['required', 'boolean'],
            'notifications.repairs' => ['required', 'boolean'],
            'notifications.notices' => ['required', 'boolean'],
        ]);

        $r->user()->update([
            'notification_preferences' => array_map(
                static fn ($enabled) => (bool) $enabled,
                $data['notifications']
            ),
        ]);

        return redirect()->route('profile.edit')->with('status', 'preferences-updated');
    }

    public function billing(Request $r)
    {
        $user = $r->user()->load('bed.room');
        $cycleDate = now()->startOfMonth();
        $requestedPeriod = (string) $r->query('period', '');
        if (preg_match('/^\d{4}-\d{2}$/', $requestedPeriod)) {
            try {
                $candidate = \Carbon\Carbon::createFromFormat('!Y-m', $requestedPeriod);
                if ($candidate && $candidate->lte(now()->startOfMonth())) $cycleDate = $candidate;
            } catch (\Throwable) {
                // Ignore invalid cycle values and keep the current billing month.
            }
        }
        $payments = $user->payments()->latest()->get();
        return view('tenant.billing', [
            'user' => $user,
            'billing' => Billing::cycle($user, $cycleDate),
            'payments' => $payments,
            'selectedPeriod' => $cycleDate->format('Y-m'),
            'paymentPeriods' => $payments->pluck('period')->filter()->map(fn ($period) => $period->format('Y-m'))->unique()->values(),
        ]);
    }

    public function storePayment(Request $r)
    {
        $user = $r->user()->load('bed');
        abort_unless($user->bed, 422, 'A bed assignment is required before submitting rent.');

        $resubmission = null;
        if ($r->filled('resubmit_id')) {
            $resubmission = $user->payments()->where('status', 'rejected')->findOrFail($r->integer('resubmit_id'));
        }

        $data = $r->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'payment_method' => ['required', Rule::in(['gcash', 'cash', 'maya', 'bank_transfer'])],
            'reference_number' => ['nullable', 'required_if:payment_method,gcash,maya', 'string', 'max:100', Rule::unique('payments', 'gcash_reference')->ignore($resubmission?->id)],
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // A rejected historical payment must be retried against its original cycle,
        // not silently moved into the current month's bill.
        $cycle = Billing::cycle($user, $resubmission?->period ?? now());
        if (Billing::amountToCents($data['amount']) > Billing::amountToCents($cycle['remaining'])) {
            return back()->withErrors(['amount' => 'Enter an amount no greater than the remaining balance of ₱'.number_format($cycle['remaining'], 2).'.'])->withInput();
        }

        $periodStart = $cycle['period']->copy()->startOfMonth();
        $attributes = [
            'bed_id' => $user->bed_id,
            'period' => $cycle['period']->toDateString(),
            'period_start' => $periodStart,
            'period_end' => $periodStart->copy()->addMonthNoOverflow(),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] === 'gcash' ? 'e_wallet' : $data['payment_method'],
            'gcash_reference' => $data['reference_number'] ?? null,
            'status' => 'pending',
            'note' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
        if ($r->hasFile('receipt')) $attributes['receipt_image'] = $r->file('receipt')->store('payment-receipts', 'public');

        if ($resubmission) $resubmission->update($attributes);
        else Payment::create($attributes + ['user_id' => $user->id]);

        return redirect()->route('tenant.billing', ['period' => $cycle['period']->format('Y-m')])->with('status', 'Payment sent for verification.');
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
        return redirect()->route('tenant.maintenance')->with('status', 'Report sent to your landlord.');
    }
}
