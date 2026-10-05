@extends('layouts.app', ['title' => 'Daftar Akun Baru - Mobeli'])

@section('content')
<div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-16 h-16 rounded-2xl object-cover shadow-md mb-3 mx-auto">
            <h1 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Daftar Akun Pelanggan</h1>
            <p class="text-sm text-neutral-500 mt-1">Dapatkan poin belanja, tier member, dan diskon eksklusif sembako.</p>
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
