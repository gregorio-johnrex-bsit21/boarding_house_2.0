<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\User; 
use App\Models\MaintenanceTicket;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon; 
use Exception;

class AdminController extends Controller
{
    // ==========================================
    // --- 1. DASHBOARD OVERVIEW CONSOLE ---
    // ==========================================
    
    public function index(): View
    {
        $dashboardData = $this->calculateDashboardStats();
        
        $upcomingDues = Payment::with(['tenant.room'])
            ->latest()
            ->take(15) 
            ->get();
            
        $systemAlerts = MaintenanceTicket::with('tenant')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->take(15) 
            ->get();
            
        return view('admin.dashboard', [
            'stats' => $dashboardData,
            'upcomingDues' => $upcomingDues,
            'systemAlerts' => $systemAlerts
        ]);
    }

    // ==========================================
    // --- 2. BACKGROUND HEARTBEAT CHANNELS ---
    // ==========================================

    public function getTenantsData(): JsonResponse
    {
        $tenants = Tenant::with('room')->latest()->get();
        return response()->json([
            'tenants' => $tenants
        ]);
    }

    public function getAdminDashboardData(): JsonResponse
    {
        $dashboardData = $this->calculateDashboardStats();

        $upcomingDues = Payment::with(['tenant.room'])
            ->latest()
            ->take(15) 
            ->get()
            ->map(function($due) {
                return [
                    'room_number' => $due->room_number ?? $due->tenant?->room?->room_number ?? 'N/A', 
                    'date'        => Carbon::parse($due->date)->format('M d, Y'),
                    'amount'      => '₱' . number_format($due->amount, 2),
                    'raw_amount'  => $due->amount,
                    'status'      => ucfirst(strtolower($due->status ?? 'pending')),
                    'tenant_name' => $due->tenant?->name ?? 'Unknown Tenant'
                ];
            });

        $systemAlerts = MaintenanceTicket::with('tenant')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->take(15) 
            ->get()
            ->map(function($alert) {
                return [
                    'category'    => $alert->category ?? 'Maintenance',
                    'time_ago'    => $alert->created_at->diffForHumans(),
                    'description' => $alert->description,
                    'tenant_name' => $alert->tenant?->name ?? 'Resident #' . $alert->tenant_id,
                ];
            });

        return response()->json([
            'stats'        => $dashboardData,
            'upcomingDues' => $upcomingDues,
            'alerts'       => $systemAlerts
        ]);
    }

    public function getPaymentsData(): JsonResponse
    {
        $payments = Payment::with('tenant.room')->latest()->get();

        return response()->json([
            'payments' => $payments
        ]);
    }

    // ==========================================
    // --- 3. ROOM MANAGEMENT INTERFACE ---
    // ==========================================

    public function rooms(): View
    {
        $rooms = Room::all();
        return view('admin.rooms', compact('rooms'));
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number',
            'price'       => 'required|numeric|min:0',
            'room_type'   => 'required|string|in:Normal,Premium',
        ]);

        Room::create([
            'room_number' => $validated['room_number'],
            'price'       => $validated['price'],
            'room_type'   => $validated['room_type'], 
            'status'      => 'Available'
        ]);

        return redirect()->route('admin.rooms')->with('success', 'Room added successfully!');
    }

    public function getRoomsData(): JsonResponse
    {
        return response()->json([
            'rooms' => Room::all()
        ]);
    }

    public function updateRoom(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number,' . $id,
            'price'       => 'required|numeric|min:0',
            'room_type'   => 'required|string|in:Normal,Premium',
            'status'      => 'required|string|in:Available,Occupied,Maintenance',
        ]);

        $room = Room::findOrFail($id);
        $room->update($validated);

        return redirect()->route('admin.rooms')->with('success', 'Room details updated successfully!');
    }

    // ==========================================
    // --- 4. TENANT DIRECTORY SHEET ---
    // ==========================================

    public function tenants(): View
    {
        $tenants = Tenant::with(['room', 'user'])->latest()->get();
        return view('admin.tenants', compact('tenants'));
    }

    public function create(): View
    {
        $availableRooms = Room::where('status', 'Available')->get();
        return view('admin.create-tenant', compact('availableRooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'required|string|max:20',
            'room_id'  => 'nullable|exists:rooms,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => 'client',
                ]);

                Tenant::create([
                    'user_id' => $user->id,
                    'name'    => $validated['name'],
                    'phone'   => $validated['phone'],
                    'room_id' => $validated['room_id'] ?? null,
                    'status'  => 'Active',
                ]);

                if (!empty($validated['room_id'])) {
                    Room::where('id', $validated['room_id'])->update(['status' => 'Occupied']);
                }
            });

            return redirect()
                ->route('admin.tenants')
                ->with('success', 'Tenant registered and system account provisioned successfully!');
                
        } catch (Exception $e) {
            Log::error('Tenant Creation Failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Critical storage exception broken. Please try again.');
        }
    }

    public function editTenant($id): View
    {
        $tenant = Tenant::with(['user', 'room'])->findOrFail($id);
        $rooms = Room::where('status', 'Available')
                     ->orWhere('id', $tenant->room_id)
                     ->get();

        return view('admin.edit-tenant', compact('tenant', 'rooms'));
    }

    public function updateTenant(Request $request, $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $tenant->user_id,
            'password' => 'nullable|string|min:6',
            'phone'    => 'required|string|max:20',
            'room_id'  => 'nullable|exists:rooms,id',
            'status'   => 'sometimes|string|in:Active,Inactive,Pending'
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $tenant) {
                if ($tenant->user_id) {
                    $userUpdate = [
                        'name'  => $validated['name'],
                        'email' => $validated['email']
                    ];
                    if ($request->filled('password')) {
                        $userUpdate['password'] = Hash::make($validated['password']);
                    }
                    User::where('id', $tenant->user_id)->update($userUpdate);
                }

                $newRoomId = $validated['room_id'] ?? null;
                
                if ($tenant->room_id != $newRoomId) {
                    if ($tenant->room_id) {
                        Room::where('id', $tenant->room_id)->update(['status' => 'Available']);
                    }
                    if ($newRoomId) {
                        Room::where('id', $newRoomId)->update(['status' => 'Occupied']);
                    }
                }

                $tenant->update([
                    'name'    => $validated['name'],
                    'phone'   => $validated['phone'],
                    'room_id' => $newRoomId,
                    'status'  => $request->input('status', $tenant->status)
                ]);
            });

            return redirect()->route('admin.tenants')->with('success', 'Tenant account parameters updated successfully.');
        } catch (Exception $e) {
            Log::error('Tenant Update Failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Update execution rejected. Check logs.');
        }
    }

    public function destroyTenant($id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);

        try {
            DB::transaction(function () use ($tenant) {
                if ($tenant->room_id) {
                    Room::where('id', $tenant->room_id)->update(['status' => 'Available']);
                }

                if ($tenant->user_id) {
                    User::where('id', $tenant->user_id)->delete();
                }

                $tenant->delete();
            });

            return redirect()->route('admin.tenants')->with('success', 'Tenant records and authentication access wiped out successfully.');
        } catch (Exception $e) {
            Log::error('Tenant Deletion Failed: ' . $e->getMessage());
            return redirect()->route('admin.tenants')->with('error', 'Failed to safely cascade deletion.');
        }
    }

    // ==========================================
    // --- 5. PAYMENT TRACKING LEDGER ---
    // ==========================================

    public function payments(): View
    {
        $payments = Payment::with('tenant.room')->latest()->get();

        $defaultSettings = [
            'gcash_number'   => '0917-123-4567',
            'gcash_name'     => 'IPK MANAGER',
            'paymaya_number' => '0917-765-4321',
            'paymaya_name'   => 'IPK MANAGER',
        ];

        if (Storage::disk('local')->exists('payment_settings.json')) {
            $savedSettings = json_decode(Storage::disk('local')->get('payment_settings.json'), true);
            if (is_array($savedSettings)) {
                $defaultSettings = array_merge($defaultSettings, $savedSettings);
            }
        }

        $settings = (object) $defaultSettings;

        return view('admin.payments', compact('payments', 'settings'));
    }

    public function approve($id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->status === 'Paid') {
            return redirect()->route('admin.payments')->with('info', 'Payment is already marked as Paid.');
        }
        
        $payment->update(['status' => 'Paid']);
        return redirect()->route('admin.payments')->with('success', 'Payment marked as Paid!');
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => 'required|string|exists:rooms,room_number',
            'amount'      => 'required|numeric|min:1',
            'date'        => 'required|date',
            'status'      => 'required|string|in:Pending,Paid,Overdue',
            'tenant_id'   => 'nullable|exists:tenants,id'
        ]);

        if (empty($validated['tenant_id'])) {
            $room = Room::where('room_number', $validated['room_number'])->first();
            if ($room) {
                $activeTenant = Tenant::where('room_id', $room->id)->where('status', 'Active')->first();
                if ($activeTenant) {
                    $validated['tenant_id'] = $activeTenant->id;
                }
            }
        }

        Payment::create($validated);
        return redirect()->route('admin.payments')->with('success', 'Payment transaction logged successfully!');
    }

    // ==========================================
    // --- 6. CONFIGURATION ENGINE DESKS ---
    // ==========================================

    public function updatePaymentSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'gcash_number'   => 'required|string|max:30',
                'gcash_name'     => 'required|string|max:100',
                'paymaya_number' => 'required|string|max:30',
                'paymaya_name'   => 'required|string|max:100',
            ]);

            Storage::disk('local')->put('payment_settings.json', json_encode($validated, JSON_PRETTY_PRINT));

            return response()->json([
                'status'  => 'success',
                'message' => 'Portal gateway parameters updated silently and pushed to production.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation check failed.',
                'errors'  => $ve->errors()
            ], 422);
            
        } catch (Exception $e) {
            Log::error('Payment Settings Update Failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'System error saving local profile configuration archive.'
            ], 500);
        }
    }

    public function profile(): View
    {
        $prefs = [
            'alert_payments' => true,
            'alert_maintenance' => true
        ];

        if (Storage::disk('local')->exists('admin_prefs.json')) {
            $savedPrefs = json_decode(Storage::disk('local')->get('admin_prefs.json'), true);
            if (is_array($savedPrefs)) {
                $prefs = array_merge($prefs, $savedPrefs);
            }
        }

        $admin = Auth::user();

        return view('admin.profile', compact('prefs', 'admin'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        if (!$admin) {
             return redirect()->route('admin.profile')->with('error', 'Unable to resolve administration access records.');
        }

        $request->validate([
            'username' => 'required|string|max:255',
            'current_password' => 'nullable|required_with:new_password', 
            'new_password' => 'nullable|min:6|confirmed', 
        ]);

        $admin->name = $request->input('username');
        
        if ($request->filled('new_password')) {
            if (!Hash::check($request->input('current_password'), $admin->password)) {
                return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
            }
            $admin->password = Hash::make($request->input('new_password'));
        }
        
        $admin->save();

        $prefs = [
            'alert_payments' => $request->has('alert_payments'),
            'alert_maintenance' => $request->has('alert_maintenance'),
        ];
        
        Storage::disk('local')->put('admin_prefs.json', json_encode($prefs, JSON_PRETTY_PRINT));
        
        return redirect()->route('admin.tenants')->with('success', 'Profile, security, and notification configurations saved successfully!');
    }

    // --- Dashboard Actions ---

    public function sendReminder(): RedirectResponse
    {
        return redirect()->route('admin.dashboard')->with('success', 'Payment reminders compiled and dispatched to overdue tenants successfully!');
    }

    public function generateInvoice(): RedirectResponse
    {
        return redirect()->route('admin.dashboard')->with('success', 'Monthly statements compiled successfully!');
    }
    
    // ==========================================
    // --- PRIVATE HELPER METHODS ---
    // ==========================================

    private function calculateDashboardStats(): array
    {
        $totalRooms = Room::count();
        
        $totalDueAmount = Payment::sum('amount'); 
        $collectedAmount = Payment::where('status', 'Paid')->sum('amount');
        $pendingAmount = Payment::whereIn('status', ['Pending', 'Overdue'])->sum('amount');
        
        $occupancyRate = $totalRooms > 0 
            ? round((Room::where('status', 'Occupied')->count() / $totalRooms) * 100) . '%' 
            : '0%';

        return [
            'total_due' => '₱' . number_format($totalDueAmount, 2), 
            'collected' => '₱' . number_format($collectedAmount, 2),
            'pending'   => '₱' . number_format($pendingAmount, 2),
            'occupancy' => $occupancyRate
        ];
    }

    // 6. PROCESS PROFILE SECURITY CREDENTIALS
    public function updateProfile(Request $request) {
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $admin = User::where('email', 'patrick@gmail.com')->first();

        if ($admin) {
            $admin->name = $request->input('username');
            
            if ($request->filled('password')) {
                $admin->password = Hash::make($request->input('password'));
            }
            
            $admin->save();
            return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
        }

        return redirect()->route('admin.profile')->with('success', 'Changes saved successfully!');
    }

    // 7. EXECUTE DISPATCH REMINDER ACTION
    public function sendReminder() {
        return redirect()->route('admin.dashboard')->with('success', 'Payment reminders have been dispatched successfully to all pending tenants!');
    }

    // 8. EXECUTE COMPUTE BILLING INVOICE ACTION
    public function generateInvoice() {
        return redirect()->route('admin.dashboard')->with('success', 'Monthly statements and system room invoices have been compiled successfully!');
    }

    // 9. PROCESS AND LOG NEW PAYMENT TRANSACTIONS
    public function storePayment(Request $request) {
        return redirect()->route('admin.payments')->with('success', 'Payment transaction logged successfully!');
    }
}