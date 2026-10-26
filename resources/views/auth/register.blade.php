@extends('layouts.app', ['title' => 'Daftar Akun Baru - Mobeli'])

@section('content')
<div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-16 h-16 rounded-2xl object-cover shadow-md mb-3 mx-auto">
            <h1 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Daftar Akun Pelanggan</h1>
            <p class="text-sm text-neutral-500 mt-1">Buat akun untuk memesan kebutuhan harian dengan lebih mudah.</p>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Nama Lengkap
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                           placeholder="Contoh: Budi Santoso"
                           class="w-full bg-neutral-50 border border-neutral-200 rounded-lg px-3.5 py-2.5 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           placeholder="budi@gmail.com"
                           class="w-full bg-neutral-50 border border-neutral-200 rounded-lg px-3.5 py-2.5 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor WhatsApp / Telepon -->
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Nomor WhatsApp / HP
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                           placeholder="081234567890"
                           class="w-full bg-neutral-50 border border-neutral-200 rounded-lg px-3.5 py-2.5 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition @error('phone') border-red-500 @enderror">
                    <p class="text-[11px] text-neutral-400 mt-1">Digunakan untuk notifikasi pesanan dan pengiriman.</p>
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               placeholder="Minimal 8 karakter"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-lg pl-3.5 pr-10 py-2.5 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition @error('password') border-red-500 @enderror">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password', this)"
                                class="absolute right-3 top-2.5 text-neutral-400 hover:text-neutral-700 p-0.5 rounded focus:outline-none transition"
                                title="Lihat/Sembunyikan Sandi">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               placeholder="Ulangi kata sandi"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-lg pl-3.5 pr-10 py-2.5 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password_confirmation', this)"
                                class="absolute right-3 top-2.5 text-neutral-400 hover:text-neutral-700 p-0.5 rounded focus:outline-none transition"
                                title="Lihat/Sembunyikan Sandi">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-primary hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm shadow-emerald-600/20 hover:scale-[1.01] active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2 mt-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Buat Akun Baru
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-neutral-200 dark:border-slate-700"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white dark:bg-slate-900 px-3 text-neutral-500 dark:text-slate-400 font-medium">atau daftar dengan</span>
                </div>
            </div>

            <!-- Google Sign Up Button -->
            <a href="{{ route('auth.google') }}" 
               class="w-full py-2.5 px-4 bg-white dark:bg-slate-800 hover:bg-neutral-50 dark:hover:bg-slate-750 text-neutral-700 dark:text-slate-100 font-semibold text-sm rounded-lg border border-neutral-300 dark:border-slate-700 shadow-2xs hover:shadow-xs transition duration-150 flex items-center justify-center gap-3 group">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Daftar dengan Google</span>
            </a>

            <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                <p class="text-xs text-neutral-600">
                    Sudah memiliki akun? 
                    <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:underline">
                        Masuk di sini
                    </a>
                </p>
            </div>
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
            ? '<i data-lucide="eye-off" class="w-4 h-4 text-emerald-600"></i>' 
            : '<i data-lucide="eye" class="w-4 h-4 text-neutral-400"></i>';
            
        if (window.lucide) {
            lucide.createIcons();
        }
    }
</script>
@endsection
