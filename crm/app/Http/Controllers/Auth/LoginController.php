<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login form
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     * 
     * Security features:
     * - CSRF protection (via middleware)
     * - Rate limiting (via LoginRequest)
     * - Session regeneration (prevents session fixation)
     * - Secure password verification (bcrypt)
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // Log successful login
        activity()
            ->causedBy(Auth::user())
            ->withProperties(['ip' => $request->ip()])
            ->log('User logged in');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request): RedirectResponse
    {
        // Log logout before clearing session
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->log('User logged out');
        }

        Auth::logout();

        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
