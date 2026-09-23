<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $roleLabel = Auth::user()->role === 'admin' ? 'Administrator' : 'Teknisi';
            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, " . Auth::user()->name . " ({$roleLabel}).");
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Helper for instant demo login during assessment
     */
    public function quickLogin(string $role): RedirectResponse
    {
        $email = $role === 'admin' ? 'admin@tracket.test' : 'teknisi@tracket.test';
        $user = \App\Models\User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();
            return redirect()->route('dashboard')
                ->with('success', "Mode Demo: Berhasil masuk sebagai {$user->name}.");
        }

        return redirect()->route('login')->with('error', 'Akun demo tidak ditemukan.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sesi.');
    }
}
