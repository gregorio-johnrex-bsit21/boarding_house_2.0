<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MaintenanceTicket; 
use App\Models\Payment; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Exception;

class ClientController extends Controller
{
    /**
     * Helper to fetch data for all client views safely.
     * Combines authentication checks, relation trees, and layout variables.
     */
    private function getResidentData(): array
    {
        $user = Auth::user();
        
        // Fail-safe check: If no user session is active, stop early
        if (!$user) {
            return $this->getEmptyDataStructure(null);
        }

        // Access relationship as a property to check if a database record actually exists
        $tenant = $user->tenantProfile;

        if (!$tenant) {
            return $this->getEmptyDataStructure($user);
        }

        // Ensure room relation is loaded gracefully
        $room = $tenant->room;

        // 🛠️ LOAD LIVE SETTINGS FROM LOCAL FILE ARCHIVE FOR THE FRONTEND
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

        // Construct metadata dynamically while handling missing structures safely
        $metadata = [
            'Room Number' => $room->room_number ?? 'N/A',
            'Room Type'   => $room->room_type ?? 'Standard',
            'Phone'       => $tenant->phone ?? 'N/A',
            'Emergency'   => $tenant->emergency_phone ?? 'N/A',
            'Joined'      => $user->created_at ? $user->created_at->format('M d, Y') : Carbon::now()->format('M d, Y'),
        ];

        // Fetch user records ordered by newest actions
        $paymentRecords = $tenant->payments()->orderBy('date', 'desc')->get();

        // Check if tenant has already paid rent for the current month
        $hasPaidThisMonth = $tenant->payments()
            ->where('status', 'Paid')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->exists();

        $currentDues = $hasPaidThisMonth ? 0.00 : ($room->price ?? ($tenant->current_balance ?? 0.00));
        $daysDueText = $hasPaidThisMonth ? 'Settled for this month' : 'Due in 5 days';

        return [
            'user'             => $user,
            'metadata'         => $metadata,
            'settings'         => $settings, 
            'roomNumber'       => $room->room_number ?? 'N/A',
            'roomType'         => $room->room_type ?? 'Standard',
            'phoneNumber'      => $tenant->phone ?? 'N/A',
            'emergencyNumber'  => $tenant->emergency_phone ?? 'N/A',
            'registrationDate' => $user->created_at ? $user->created_at->format('M d, Y') : Carbon::now()->format('M d, Y'),
            'currentDues'      => $currentDues, 
            'daysDueText'      => $daysDueText,
            'recentActions'    => $paymentRecords,
            'payments'         => $paymentRecords
        ];
    }

    /**
     * Fallback blank template helper to keep views from throwing fatal variable bugs
     */
    private function getEmptyDataStructure($user): array 
    {
        $defaultSettings = [
            'gcash_number'   => '0917-123-4567',
            'gcash_name'     => 'IPK MANAGER',
            'paymaya_number' => '0917-765-4321',
            'paymaya_name'   => 'IPK MANAGER',
        ];

        return [
            'user'             => $user,
            'metadata'         => [
                'Room Number' => 'N/A', 
                'Room Type'   => 'Standard', 
                'Phone'       => 'N/A', 
                'Emergency'   => 'N/A', 
                'Joined'      => Carbon::now()->format('M d, Y')
            ],
            'settings'         => (object) $defaultSettings, 
            'roomNumber'       => 'N/A', 
            'roomType'         => 'Standard', 
            'phoneNumber'      => 'N/A', 
            'emergencyNumber'  => 'N/A',
            'registrationDate' => Carbon::now()->format('M d, Y'), 
            'currentDues'      => 0.00, 
            'daysDueText'      => 'N/A', 
            'recentActions'    => collect(),
            'payments'         => collect()
        ];
    }

    /**
     * 🌟 SILENT BACKGROUND REAL-TIME INTERVAL UPDATE FEED FOR CLIENT PORTAL
     */
    public function getDynamicDashboardData(): JsonResponse
    {
        $user = Auth::user();
        $tenant = $user ? $user->tenantProfile : null;

        if (!$tenant) {
            return response()->json([
                'status'        => 'success',
                'currentDues'   => '0.00',
                'daysDueText'   => 'No Profile Assigned',
                'ticketCount'   => 0,
                'recentActions' => [],
                'settings'      => (object) [
                    'gcash_number'   => '0917-123-4567',
                    'gcash_name'     => 'IPK MANAGER',
                    'paymaya_number' => '0917-765-4321',
                    'paymaya_name'   => 'IPK MANAGER',
                ]
            ]);
        }

        // 🛠️ LOAD LIVE SETTINGS FROM LOCAL FILE ARCHIVE (Synchronized to prevent 500 errors)
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

        $hasPaidThisMonth = $tenant->payments()
            ->where('status', 'Paid')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->exists();

        $rawDues = $hasPaidThisMonth ? 0.00 : ($tenant->room ? $tenant->room->price : ($tenant->current_balance ?? 0.00));
        $daysDueText = $hasPaidThisMonth ? 'Settled' : 'Due in 5 days';

        return response()->json([
            'status'        => 'success', 
            'currentDues'   => number_format($rawDues, 2),
            'daysDueText'   => $daysDueText, 
            'ticketCount'   => MaintenanceTicket::where('tenant_id', $tenant->id)->count(),
            'recentActions' => $tenant->payments()->orderBy('date', 'desc')->take(5)->get(),
            'settings'      => $settings 
        ]);
    }

    /**
     * 🌟 SILENT BACKGROUND PAYMENT INFO FETCH (No Page Reload)
     */
    public function getLatestPaymentInfo(): JsonResponse
    {
        $user = Auth::user();
        $tenant = $user ? $user->tenantProfile : null;

        if (!$tenant) {
            return response()->json(['error' => 'No profile assigned'], 404);
        }

        // 1. Calculate Dues (Using exact existing logic)
        $hasPaidThisMonth = $tenant->payments()
            ->where('status', 'Paid')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->exists();

        $rawDues = $hasPaidThisMonth ? 0.00 : ($tenant->room ? $tenant->room->price : ($tenant->current_balance ?? 0.00));

        // 2. Load Live Settings
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

        // 3. Return the clean JSON payload
        return response()->json([
            'currentDues'    => number_format($rawDues, 2, '.', ''), 
            'gcash_number'   => $defaultSettings['gcash_number'],
            'paymaya_number' => $defaultSettings['paymaya_number'],
        ]);
    }

    // ==========================================
    // --- Page Routing ---
    // ==========================================

    /**
     * Display the portal login panel. Redirects active sessions automatically.
     */
    public function showLogin(): View|RedirectResponse
    { 
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin' || $user->is_admin == 1) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('client.dashboard');
        }
        return view('client.login'); 
    }

    /**
     * Load core Resident Overview system engine.
     */
    public function index(): View|RedirectResponse
    { 
        $user = Auth::user();
        if ($user && ($user->role === 'admin' || $user->is_admin == 1)) {
            return redirect()->route('admin.dashboard');
        }
        return view('client.dashboard', $this->getResidentData()); 
    }

    /**
     * Load manual invoice payment submittal system view.
     */
    public function paymentPage(): View|RedirectResponse
    { 
        $user = Auth::user();
        if ($user && ($user->role === 'admin' || $user->is_admin == 1)) {
            return redirect()->route('admin.dashboard'); 
        }
        return view('client.payment', $this->getResidentData()); 
    }

    /**
     * Load historical account transactions ledger view.
     */
    public function historyPage(): View|RedirectResponse
    { 
        $user = Auth::user();
        if ($user && ($user->role === 'admin' || $user->is_admin == 1)) {
            return redirect()->route('admin.dashboard');
        }
        return view('client.history', $this->getResidentData()); 
    }

    /**
     * Load user profile parameter configurations menu.
     */
    public function settingsPage(): View|RedirectResponse
    { 
        $user = Auth::user();
        if ($user && ($user->role === 'admin' || $user->is_admin == 1)) {
            return redirect()->route('admin.dashboard');
        }
        return view('client.settings', $this->getResidentData()); 
    }

    // ==========================================
    // --- Logic Methods ---
    // ==========================================

    /**
     * Processes security authentication matches for resident portals.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'], 
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            if ((isset($user->role) && strtolower(trim($user->role)) === 'admin') || (isset($user->is_admin) && $user->is_admin == 1)) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('client.dashboard');
        }
        
        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
    }

    /**
     * Update account registration variables safely across all system layers.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name'  => 'required|string|max:255', 
            'email' => 'required|email|unique:users,email,' . $user->id
        ]);
        
        try {
            // 🔄 Keep User record and Tenant profile records completely synchronized
            DB::transaction(function () use ($user, $validated) {
                $user->update([
                    'name'  => $validated['name'],
                    'email' => $validated['email']
                ]);

                if ($user->tenantProfile) {
                    $user->tenantProfile->update([
                        'name' => $validated['name']
                    ]);
                }
            });

            return back()->with('success', 'Profile updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update profile settings.');
        }
    }

    /**
     * Dispatch structural maintenance support tickets to administrative overview desks.
     */
    public function storeTicket(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'    => ['required', 'string'],
            'description' => ['required', 'string', 'min:10']
        ]);

        $tenant = Auth::user()->tenantProfile;

        // 🛑 Safeguard against unlinked structural database accounts
        if (!$tenant) {
            return redirect()->back()->with('error', 'Action denied. Your account is not bound to a resident profile.');
        }

        MaintenanceTicket::create([
            'tenant_id'   => $tenant->id,
            'category'    => $validated['category'],
            'description' => $validated['description'],
            'status'      => 'Pending'
        ]);

        return redirect()->back()->with('success', 'Your support request has been submitted!');
    }

    /**
     * Submit manual deposit receipts alongside unique transaction validation strings.
     */
    public function submitPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'amount'           => ['required', 'numeric', 'min:1'], 
            'reference_number' => ['required', 'string', 'unique:payments,reference_number'],
            'receipt'          => ['required', 'image', 'max:4096'] 
        ]);
        
        $tenant = Auth::user()->tenantProfile;

        // 🛑 Safeguard against unlinked structural database accounts
        if (!$tenant) {
            return redirect()->back()->with('error', 'Action denied. Your account is not bound to a resident profile.');
        }

        // Save image securely inside 'storage/app/public/receipts' destination pathing
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        Payment::create([
            'tenant_id'        => $tenant->id,
            'room_number'      => $tenant->room?->room_number ?? 'N/A', 
            'amount'           => $request->amount,
            'reference_number' => $request->reference_number,
            'receipt_path'     => $receiptPath, 
            'status'           => 'Pending',
            'date'             => Carbon::now()->format('Y-m-d H:i:s'), 
        ]);

        return redirect()->route('client.history')->with('success', 'Payment submitted successfully!');
    }

    /**
     * Terminate active user sessions securely.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login');
    }
}