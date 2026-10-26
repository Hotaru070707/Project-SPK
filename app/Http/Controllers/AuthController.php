<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'Akun Anda dinonaktifkan oleh administrator.']);
            }

            return $this->redirectBasedOnRole($user)
                ->with('success', "Login berhasil. Selamat datang kembali, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Selamat datang di Mobeli! Pendaftaran akun Anda berhasil.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda berhasil keluar dari akun.')
            ->with('status', 'Anda berhasil keluar dari akun.');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $driver = Socialite::driver('google');

            try {
                $googleUser = $driver->user();
            } catch (InvalidStateException $e) {
                // Sesi state mismatch pada redirect top-level antar origin, fallback ke stateless
                $googleUser = $driver->stateless()->user();
            }
        } catch (Throwable $e) {
            Log::error('Google OAuth callback failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            $errorMessage = config('app.debug')
                ? 'Gagal masuk menggunakan akun Google: '.$e->getMessage()
                : 'Gagal masuk menggunakan akun Google. Silakan coba kembali atau gunakan email dan password.';

            return redirect()->route('login')
                ->withErrors(['email' => $errorMessage]);
        }

        // 1. Cari user berdasarkan google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            // 2. Jika belum ada google_id, cari berdasarkan email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Tautkan google_id ke akun yang sudah ada
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                ]);
            } else {
                // 3. Daftarkan otomatis sebagai customer baru
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'Pengguna Google',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'role' => 'customer',
                    'is_active' => true,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }
        }

        if (! $user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda dinonaktifkan oleh administrator.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user)
            ->with('success', "Login berhasil. Selamat datang, {$user->name}!");
    }

    protected function redirectBasedOnRole(User $user)
    {
        if ($user->isOwner()) {
            return redirect()->intended('/admin');
        } elseif ($user->isCashier()) {
            return redirect()->intended('/kasir');
        }

        return redirect()->route('home');
    }
}
