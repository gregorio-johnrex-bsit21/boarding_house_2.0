<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/
Route::get('/client/login', [ClientController::class, 'showLogin'])->name('client.login');
Route::post('/client/login/submit', [ClientController::class, 'login'])->name('client.login.submit');

/*
|--------------------------------------------------------------------------
| Protected Resident Routes (Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    
    // 1. Core Dashboard & Profile
    Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
    
    // 2. Online Payment Portal
    Route::get('/payment', [ClientController::class, 'paymentPage'])->name('payment');
    Route::post('/payment/submit', [ClientController::class, 'submitPayment'])->name('payment.submit');
    
    // 3. Complete Billing History
    Route::get('/history', [ClientController::class, 'historyPage'])->name('history');
    
    // 4. Settings (About Us & Help)
    Route::get('/settings', [ClientController::class, 'settingsPage'])->name('settings');
    
    // 🌟 Help Desk Route to process the support form data
    Route::post('/ticket/store', [ClientController::class, 'storeTicket'])->name('ticket.store');
    
    // Session Termination
    Route::get('/logout', [ClientController::class, 'logout'])->name('logout');
});