@extends('layouts.app', ['title' => 'Lupa Kata Sandi - Mobeli'])

@section('content')
<div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-extrabold text-2xl shadow-lg shadow-emerald-600/20 mb-3 transition transform hover:scale-105">
                <i data-lucide="key-round" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading text-neutral-900 tracking-tight">Lupa Kata Sandi</h1>
            <p class="text-sm text-neutral-500 mt-1">
                Masukkan email Anda. Kami akan mengirimkan <strong>6 digit kode OTP</strong> untuk verifikasi reset kata sandi.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 sm:p-8">
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-start gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                    <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Alamat Email Terdaftar
                    </label>
                    <div class="relative">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               placeholder="contoh: nama@email.com"
                               class="w-full bg-neutral-50 border border-neutral-200 rounded-xl pl-10 pr-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('email') border-red-500 @enderror">
                        <i data-lucide="mail" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-3.5"></i>
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 mt-1.5 font-medium flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-emerald-600/25 hover:shadow-lg hover:shadow-emerald-600/30 hover:scale-[1.01] active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Kirim Kode OTP
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                <p class="text-xs text-neutral-600">
                    Ingat kembali kata sandi Anda? 
                    <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                        Kembali ke Halaman Masuk
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
