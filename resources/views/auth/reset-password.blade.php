@extends('layouts.app', ['title' => 'Buat Sandi Baru - Mobeli'])

@section('content')
<div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-extrabold text-2xl shadow-lg shadow-emerald-600/20 mb-3 transition transform hover:scale-105">
                <i data-lucide="lock-keyhole" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Buat Kata Sandi Baru</h1>
            <p class="text-sm text-neutral-500 mt-1">
                OTP berhasil diverifikasi. Silakan masukkan kata sandi baru untuk akun Anda.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 sm:p-8">
            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-start gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                    <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('password.reset.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email (Readonly) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Alamat Email Akun
                    </label>
                    <div class="relative">
                        <input type="email" value="{{ $email }}" readonly
                               class="w-full bg-neutral-100 border border-neutral-200 rounded-xl pl-10 pr-4 py-3 text-sm text-neutral-600 font-medium cursor-not-allowed">
                        <i data-lucide="mail" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-3.5"></i>
                    </div>
                </div>

                <!-- Password Baru -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Kata Sandi Baru
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required autofocus
                               placeholder="Minimal 8 karakter"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-xl pl-10 pr-12 py-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('password') border-red-500 @enderror">
                        <i data-lucide="lock" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-3.5"></i>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password', this)"
                                class="absolute right-3.5 top-3 text-neutral-400 hover:text-neutral-700 p-0.5 rounded-lg focus:outline-none transition"
                                title="Lihat/Sembunyikan Sandi">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1.5 font-medium flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Ulangi Kata Sandi Baru
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               placeholder="Ulangi kata sandi di atas"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-xl pl-10 pr-12 py-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <i data-lucide="shield-check" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-3.5"></i>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password_confirmation', this)"
                                class="absolute right-3.5 top-3 text-neutral-400 hover:text-neutral-700 p-0.5 rounded-lg focus:outline-none transition"
                                title="Lihat/Sembunyikan Sandi">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-emerald-600/25 hover:shadow-lg hover:shadow-emerald-600/30 hover:scale-[1.01] active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2 mt-4">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Kata Sandi Baru
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        btn.innerHTML = isPassword 
            ? '<i data-lucide="eye-off" class="w-5 h-5 text-emerald-600"></i>' 
            : '<i data-lucide="eye" class="w-5 h-5 text-neutral-400"></i>';
            
        if (window.lucide) {
            lucide.createIcons();
        }
    }
</script>
@endsection
