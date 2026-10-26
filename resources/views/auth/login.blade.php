@extends('layouts.app', ['title' => 'Masuk Akun - Mobeli'])

@section('content')
<div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-16 h-16 rounded-2xl object-cover shadow-md mb-3 mx-auto">
            <h1 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Masuk ke Mobeli</h1>
            <p class="text-sm text-neutral-500 mt-1">Satu akun untuk belanja online, kelola kasir, atau akses analitik pemilik.</p>
        </div>

        <!-- Quick Demo Switcher (Sangat Membantu Evaluasi & Presentasi!) -->
        <div class="mb-6 p-3.5 bg-emerald-50/70 border border-emerald-200/80 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-emerald-600"></i> Akun Demo Cepat
                </span>
                <span class="text-[11px] text-emerald-700">Klik untuk isi otomatis</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillLogin('owner@tokotopsis.id', 'password123')" 
                        class="p-2 text-left rounded-lg bg-white border border-emerald-200 hover:border-emerald-400 hover:shadow-xs transition">
                    <div class="text-[11px] font-bold text-neutral-900 flex items-center gap-1">
                        👑 Owner
                    </div>
                    <div class="text-[9px] text-neutral-500 truncate">Hendra (Pemilik)</div>
                </button>

                <button type="button" onclick="fillLogin('kasir@tokotopsis.id', 'password123')" 
                        class="p-2 text-left rounded-lg bg-white border border-emerald-200 hover:border-emerald-400 hover:shadow-xs transition">
                    <div class="text-[11px] font-bold text-neutral-900 flex items-center gap-1">
                        ⚡ Kasir
                    </div>
                    <div class="text-[9px] text-neutral-500 truncate">Dewi (POS Shift)</div>
                </button>

                <button type="button" onclick="fillLogin('budi@gmail.com', 'password123')" 
                        class="p-2 text-left rounded-lg bg-white border border-emerald-200 hover:border-emerald-400 hover:shadow-xs transition">
                    <div class="text-[11px] font-bold text-neutral-900 flex items-center gap-1">
                        🛒 Customer
                    </div>
                    <div class="text-[9px] text-neutral-500 truncate">Pak Budi (Member)</div>
                </button>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               placeholder="nama@email.com"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-lg pl-10 pr-3.5 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition @error('email') border-red-500 @enderror">
                        <i data-lucide="mail" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-3"></i>
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-neutral-700">
                            Kata Sandi
                        </label>
                        <a href="{{ url('/lupa-password') }}" class="text-xs text-emerald-600 hover:underline">
                            Lupa sandi?
                        </a>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               placeholder="••••••••"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-lg pl-10 pr-10 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white transition">
                        <i data-lucide="lock" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-3"></i>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password', this)"
                                class="absolute right-3 top-2.5 text-neutral-400 hover:text-neutral-700 p-0.5 rounded focus:outline-none transition"
                                title="Lihat/Sembunyikan Sandi">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" 
                           class="w-4 h-4 rounded border-neutral-300 text-primary focus:ring-primary/30">
                    <label for="remember" class="ml-2 text-xs text-neutral-600 select-none">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-primary hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm shadow-emerald-600/20 hover:scale-[1.01] active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i> Masuk Sekarang
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-neutral-200 dark:border-slate-700"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white dark:bg-slate-900 px-3 text-neutral-500 dark:text-slate-400 font-medium">atau masuk dengan</span>
                </div>
            </div>

            <!-- Google Login Button -->
            <a href="{{ route('auth.google') }}" 
               class="w-full py-2.5 px-4 bg-white dark:bg-slate-800 hover:bg-neutral-50 dark:hover:bg-slate-750 text-neutral-700 dark:text-slate-100 font-semibold text-sm rounded-lg border border-neutral-300 dark:border-slate-700 shadow-2xs hover:shadow-xs transition duration-150 flex items-center justify-center gap-3 group">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Masuk dengan Google</span>
            </a>

            <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                <p class="text-xs text-neutral-600">
                    Belum punya akun pelanggan? 
                    <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:underline">
                        Daftar disini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function fillLogin(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }

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
