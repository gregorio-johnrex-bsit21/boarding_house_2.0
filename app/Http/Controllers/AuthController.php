<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLogin() {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('admin.login'); 
    }

    public function login(Request $request): RedirectResponse {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our system records.',
        ])->onlyInput('email');
    }

    private function redirectBasedOnRole($user) {
        $role = isset($user->role) ? strtolower(trim($user->role)) : '';
        $isAdminFlag = isset($user->is_admin) ? (int)$user->is_admin : 0;

        // If explicitly matching admin properties, grant entrance to console
        if ($role === 'admin' || $isAdminFlag === 1) {
            return redirect()->route('admin.dashboard');
        }

        // Direct regular clients out of admin dashboard space
        return redirect()->route('client.dashboard');
    }

    public function logout(Request $request): RedirectResponse {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login');
    }
}