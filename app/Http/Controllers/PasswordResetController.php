<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan halaman form input email lupa password
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Generate Kode OTP 6 Digit dan kirim ke email pengguna
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Alamat email ini tidak terdaftar dalam sistem toko kami.',
            ])->withInput();
        }

        // Generate 6 Digit Angka OTP
        $otp = (string) random_int(100000, 999999);

        // Simpan ke database password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => now(),
            ]
        );

        // Simpan email ke session untuk tahap verifikasi
        session(['reset_email' => $email]);

        // Kirim email via SMTP
        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user->name));
        } catch (\Throwable $e) {
            Log::error('SMTP OTP Error: '.$e->getMessage(), ['exception' => $e]);

            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP: '.$e->getMessage().'. Pastikan jaringan mengizinkan koneksi email.',
            ])->withInput();
        }

        return redirect()->route('password.otp.form')->with([
            'status' => 'Kode verifikasi OTP 6-digit telah dikirimkan ke email '.$email.'. Silakan periksa kotak masuk atau folder spam Anda.',
        ]);
    }

    /**
     * Tampilkan halaman input kode OTP
     */
    public function showOtpForm()
    {
        $email = session('reset_email');
        if (! $email) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Silakan masukkan email Anda terlebih dahulu.',
            ]);
        }

        return view('auth.verify-otp', compact('email'));
    }

    /**
     * Verifikasi kode OTP 6 Digit yang dimasukkan pengguna
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ], [
            'otp.required' => 'Kode OTP 6-digit wajib diisi.',
            'otp.numeric' => 'Kode OTP harus berupa angka.',
            'otp.digits' => 'Kode OTP harus tepat 6 digit.',
        ]);

        $email = session('reset_email');
        if (! $email) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Sesi reset password telah berakhir. Silakan ulangi.',
            ]);
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record) {
            return back()->withErrors([
                'otp' => 'Permintaan OTP tidak ditemukan. Silakan kirim ulang kode baru.',
            ]);
        }

        // Cek kedaluwarsa (15 menit)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return back()->withErrors([
                'otp' => 'Kode OTP telah kadaluarsa (melebihi 15 menit). Silakan klik kirim ulang kode.',
            ]);
        }

        // Cek kecocokan OTP
        if ($record->token !== trim($request->otp)) {
            return back()->withErrors([
                'otp' => 'Kode OTP yang Anda masukkan salah. Periksa kembali email Anda.',
            ])->withInput();
        }

        // OTP Valid! Tandai verifikasi berhasil di session
        session(['otp_verified' => true]);

        return redirect()->route('password.reset.form')->with(
            'status', 'Kode OTP berhasil diverifikasi! Silakan buat kata sandi baru untuk akun Anda.'
        );
    }

    /**
     * Kirim ulang kode OTP
     */
    public function resendOtp()
    {
        $email = session('reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return redirect()->route('password.request');
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => now(),
            ]
        );

        // Kirim ulang email via SMTP
        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user->name));
        } catch (\Throwable $e) {
            Log::error('SMTP Resend Error: '.$e->getMessage(), ['exception' => $e]);

            return back()->withErrors([
                'otp' => 'Gagal mengirim ulang kode OTP ke email: '.$e->getMessage(),
            ]);
        }

        return redirect()->route('password.otp.form')->with([
            'status' => 'Kode OTP baru telah berhasil dikirimkan ke email '.$email.'. Silakan periksa kotak masuk Anda.',
        ]);
    }

    /**
     * Tampilkan form input kata sandi baru setelah OTP valid
     */
    public function showResetForm()
    {
        $email = session('reset_email');
        $verified = session('otp_verified');

        if (! $email || ! $verified) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Harap verifikasi kode OTP terlebih dahulu.',
            ]);
        }

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Simpan kata sandi baru ke database
     */
    public function resetPassword(Request $request)
    {
        $email = session('reset_email');
        $verified = session('otp_verified');

        if (! $email || ! $verified) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = User::where('email', $email)->first();
        if (! $user) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Pengguna tidak ditemukan.',
            ]);
        }

        // Update password baru
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus token reset
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Bersihkan session
        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('login')->with(
            'status', 'Kata sandi berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.'
        );
    }
}
