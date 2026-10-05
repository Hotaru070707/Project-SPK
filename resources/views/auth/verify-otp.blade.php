@extends('layouts.app', ['title' => 'Verifikasi Kode OTP - Mobeli'])

@section('content')
<div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-extrabold text-2xl shadow-lg shadow-emerald-600/20 mb-3 transition transform hover:scale-105">
                <i data-lucide="shield-check" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Verifikasi Kode OTP</h1>
            <p class="text-sm text-neutral-500 mt-1">
                Masukkan 6-digit kode verifikasi yang telah dikirimkan ke:
            </p>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-neutral-100 rounded-full text-xs font-semibold text-neutral-800 mt-2">
                <i data-lucide="mail" class="w-3.5 h-3.5 text-neutral-500"></i>
                <span>{{ $email }}</span>
            </div>
        </div>

        <!-- Card Form -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 sm:p-8">
            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-start gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                    <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                </div>
            @endif

            @if (session('demo_otp'))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">
                    <div class="flex items-center gap-2 font-bold mb-1 text-amber-800">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                        <span>Mode Pengujian / Demo:</span>
                    </div>
                    <p class="text-xs text-amber-700 mb-2 leading-relaxed">
                        {{ session('smtp_warning') ?? 'Kode OTP Anda:' }}
                    </p>
                    <div class="flex items-center justify-between bg-white/80 p-2.5 rounded-lg border border-amber-200">
                        <span class="font-mono text-xl font-extrabold tracking-widest text-neutral-900">{{ session('demo_otp') }}</span>
                        <button type="button" 
                                onclick="document.getElementById('otp').value='{{ session('demo_otp') }}'"
                                class="text-xs font-semibold px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition">
                            Isi Otomatis
                        </button>
                    </div>
                </div>
            @endif

            <form action="{{ route('password.otp.verify') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="otp" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-2 text-center">
                        Masukkan 6 Digit Kode OTP
                    </label>
                    
                    <div class="relative max-w-[280px] mx-auto">
                        <input type="text" 
                               name="otp" 
                               id="otp" 
                               maxlength="6" 
                               value="{{ old('otp') }}" 
                               required 
                               autofocus
                               inputmode="numeric"
                               pattern="[0-9]*"
                               autocomplete="one-time-code"
                               placeholder="· · · · · ·"
                               class="w-full text-center tracking-[12px] font-mono text-2xl font-bold bg-neutral-50 border border-neutral-300 rounded-xl py-3 text-neutral-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('otp') border-red-500 @enderror">
                    </div>
                    
                    @error('otp')
                        <p class="text-xs text-red-600 mt-2 font-medium text-center flex items-center justify-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}
                        </p>
                    @enderror
                    
                    <p class="text-[11px] text-neutral-400 text-center mt-2">
                        Kode OTP berlaku selama 15 menit.
                    </p>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-emerald-600/25 hover:shadow-lg hover:shadow-emerald-600/30 hover:scale-[1.01] active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Verifikasi OTP & Lanjutkan
                </button>
            </form>

            <!-- Kirim Ulang OTP -->
            <div class="mt-6 pt-5 border-t border-neutral-100 flex items-center justify-between text-xs text-neutral-500">
                <span>Tidak menerima kode?</span>
                <form action="{{ route('password.otp.resend') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline inline-flex items-center gap-1">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i> Kirim Ulang OTP
                    </button>
                </form>
            </div>
            
            <div class="mt-3 text-center">
                <a href="{{ route('password.request') }}" class="text-xs text-neutral-400 hover:text-neutral-600">
                    &larr; Ganti alamat email
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Hanya izinkan input angka pada field OTP
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
</script>
@endsection
