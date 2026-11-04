<?php
use App\Http\Controllers\{AdminDashboardController as Admin, MapApiController, ProfileController, TenantDashboardController as Tenant};
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {
    // Replaces Breeze's default /dashboard route
    Route::get('/dashboard', fn () => auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard') : redirect()->route('tenant.home'))->name('dashboard');

    // JSON for the pages' own fetch() calls (session + CSRF, no API tokens)
    Route::get('/map/statuses', [MapApiController::class, 'statuses'])->name('map.statuses');

    // Breeze profile pages (used by the tenant "Account" tab)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [Admin::class, 'index'])->name('dashboard');
        Route::post('/tenants', [Admin::class, 'storeTenant'])->name('tenants.store');
        Route::post('/tenants/{user}/cash', [Admin::class, 'recordCash'])->name('tenants.cash');
        Route::post('/payments/{payment}/status', [Admin::class, 'updatePaymentStatus'])->name('payments.status');
        Route::post('/beds/{bed}/assign', [Admin::class, 'assign'])->name('beds.assign');
        Route::post('/beds/{bed}/vacate', [Admin::class, 'vacate'])->name('beds.vacate');
        Route::post('/settings', [Admin::class, 'updateSettings'])->name('settings');
        Route::post('/device-catalog', [Admin::class, 'storeDeviceCatalog'])->name('device-catalog.store');
        Route::patch('/device-catalog/{deviceCatalog}', [Admin::class, 'updateDeviceCatalog'])->name('device-catalog.update');
        Route::delete('/device-catalog/{deviceCatalog}', [Admin::class, 'destroyDeviceCatalog'])->name('device-catalog.destroy');
        Route::post('/device-declarations/{deviceDeclaration}/approve', [Admin::class, 'approveDeviceDeclaration'])->name('device-declarations.approve');
        Route::post('/device-declarations/{deviceDeclaration}/reject', [Admin::class, 'rejectDeviceDeclaration'])->name('device-declarations.reject');
        Route::post('/electricity-bills', [Admin::class, 'storeElectricityBill'])->name('electricity-bills.store');
        Route::patch('/beds/{bed}/status', [Admin::class, 'updateBedStatus'])->name('beds.status');
        Route::patch('/maintenance/{maintenanceRequest}', [Admin::class, 'updateMaintenance'])->name('maintenance.update');
    });

    Route::middleware('role:tenant')->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/', [Tenant::class, 'home'])->name('home');
        Route::get('/map', [Tenant::class, 'map'])->name('map');
        Route::get('/wifi-devices', [Tenant::class, 'wifiDevices'])->name('wifi-devices');
        Route::post('/devices', [Tenant::class, 'storeDeviceDeclaration'])->name('devices.store');
        Route::delete('/devices/{deviceDeclaration}', [Tenant::class, 'destroyDeviceDeclaration'])->name('devices.destroy');
        Route::post('/device-declarations', [Tenant::class, 'submitDeviceDeclaration'])->name('device-declarations.store');
        Route::delete('/device-declarations/{deviceDeclaration}', [Tenant::class, 'cancelDeviceDeclaration'])->name('device-declarations.destroy');
        Route::patch('/preferences', [Tenant::class, 'updatePreferences'])->name('preferences.update');
        Route::get('/billing', [Tenant::class, 'billing'])->name('billing');
        Route::post('/billing', [Tenant::class, 'storePayment'])->name('billing.store');
        Route::get('/maintenance', [Tenant::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance', [Tenant::class, 'storeMaintenance'])->name('maintenance.store');
    });
});

require __DIR__.'/auth.php'; // Breeze
