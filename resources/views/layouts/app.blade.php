<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mobeli - Toko Kelontong Modern & SPK TOPSIS' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- Tailwind CSS CDN for instant rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#16a34a',
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        },
                        secondary: {
                            DEFAULT: '#f97316',
                            500: '#f97316',
                            600: '#ea580c',
                        },
                        ivory: '#fafaf7',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #fafaf7;
            color: #1e293b;
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .price-font {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* Global Dark Mode Overrides */
        html.dark {
            color-scheme: dark;
        }
        html.dark body {
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }
        html.dark .bg-white, html.dark header.bg-white\/95 {
            background-color: #1e293b !important;
        }
        html.dark .bg-neutral-50, html.dark .bg-slate-50, html.dark .bg-neutral-100 {
            background-color: #0f172a !important;
        }
        html.dark .border-neutral-200, html.dark .border-neutral-100 {
            border-color: #334155 !important;
        }
        html.dark .text-neutral-900, html.dark .text-neutral-800 {
            color: #f8fafc !important;
        }
        html.dark .text-neutral-700, html.dark .text-neutral-600 {
            color: #cbd5e1 !important;
        }
        html.dark .text-neutral-500 {
            color: #94a3b8 !important;
        }
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">
    <!-- Top Announcement Bar -->
    <div class="bg-emerald-800 text-white text-xs py-2 px-4 text-center font-medium">
        <span>🏪 Belanja Sembako Lengkap & Murah di Mobeli. Pesan Online, Langsung Ambil di Toko Bebas Antri!</span>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center gap-2 sm:gap-3">
                <button onclick="window.history.back()" class="p-2 -ml-2 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-lg transition" title="Kembali">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </button>
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-10 h-10 rounded-xl object-cover shadow-sm group-hover:scale-105 transition">
                <div>
                    <span class="font-heading font-extrabold text-xl text-neutral-900 tracking-tight flex items-center gap-1.5">
                        Mobeli
                    </span>
                    <p class="text-[11px] text-neutral-500 leading-tight">Kelontong & Keputusan Cerdas</p>
                </div>
            </a>
            </div>

            <!-- Search Form - Removed from header based on user request -->

            <!-- Nav Actions & User Info -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/produk') }}" class="text-sm font-medium text-neutral-700 hover:text-primary transition hidden sm:inline-flex items-center gap-1">
                    <i data-lucide="grid" class="w-4 h-4"></i> Katalog
                </a>

                @auth
                    @if(auth()->user()->isOwner())
                        <a href="{{ url('/admin') }}" class="px-3 py-1.5 rounded-lg bg-neutral-900 text-white text-xs font-semibold hover:bg-neutral-800 transition flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-3.5 h-3.5"></i> Dashboard Owner
                        </a>
                    @elseif(auth()->user()->isCashier())
                        <a href="{{ url('/kasir') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5">
                            <i data-lucide="calculator" class="w-3.5 h-3.5"></i> POS Kasir
                        </a>
                    @endif

                    <!-- Keranjang -->
                    <a href="{{ url('/akun/keranjang') }}" class="relative p-2 rounded-lg text-neutral-700 dark:text-slate-300 hover:bg-neutral-100 dark:hover:bg-slate-800 transition">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        <span id="cart-counter" class="absolute -top-1 -right-1 bg-secondary text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                            {{ session('cart_count', 0) }}
                        </span>
                    </a>

                    <!-- Theme Toggle -->
                    <button type="button" onclick="toggleTheme()" title="Ganti Mode Gelap / Terang" class="p-2 rounded-lg text-neutral-600 dark:text-amber-400 hover:bg-neutral-100 dark:hover:bg-slate-800 transition">
                        <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                        <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                    </button>

                    <!-- User Menu -->
                    <div class="flex items-center gap-2 pl-2 border-l border-neutral-200">
                        <a href="{{ route('profile.edit') }}" title="Buka Profil Saya" class="flex items-center gap-2 text-right group hover:opacity-85 transition">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-emerald-500/40">
                            @else
                                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="text-right hidden sm:block">
                                <p class="text-xs font-bold text-neutral-900 group-hover:text-emerald-700 transition leading-tight">{{ auth()->user()->name }}</p>
                                <span class="text-[10px] font-medium text-emerald-700 uppercase tracking-wider bg-emerald-50 px-1.5 py-0.5 rounded">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Keluar Akun" class="p-2 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs font-semibold text-neutral-700 hover:text-neutral-900 hover:bg-neutral-100 rounded-lg transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-semibold text-white bg-primary hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 hover:scale-[1.02] active:scale-[0.98] transition">
                        Daftar Akun
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-900 text-sm flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content Slot -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-neutral-900 text-neutral-300 pt-12 pb-8 border-t border-neutral-800 mt-16 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-white font-heading font-bold text-lg">
                        <span class="w-7 h-7 rounded-lg bg-primary text-white flex items-center justify-center text-sm font-extrabold">M</span>
                        Mobeli
                    </div>
                    @if(!request()->is('login', 'daftar', 'masuk'))
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Sistem Informasi Toko Kelontong Modern dengan integrasi POS Kasir, Toko Online, dan Analisis Keputusan Cerdas Metode TOPSIS.
                    </p>
                    @endif
                    <div class="text-xs text-neutral-400">
                        📍 Jl. Merdeka No. 45, Sukajadi<br>
                        📞 WhatsApp: 0812-3456-7890
                    </div>
                </div>

                @if(!request()->is('login', 'daftar', 'masuk'))

                <div>
                    <h4 class="font-heading font-semibold text-white text-xs uppercase tracking-wider mb-3">Navigasi Toko</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ url('/') }}" class="hover:text-white transition">Beranda Utama</a></li>
                        <li><a href="{{ url('/produk') }}" class="hover:text-white transition">Katalog Produk Lengkap</a></li>
                        <li><a href="{{ url('/produk?kategori=sembako') }}" class="hover:text-white transition">Sembako & Kebutuhan Pokok</a></li>
                        <li><a href="{{ url('/tentang') }}" class="hover:text-white transition">Tentang Kami</a></li>
                        <li><a href="{{ url('/kontak') }}" class="hover:text-white transition">Hubungi Kami</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-heading font-semibold text-white text-xs uppercase tracking-wider mb-3">Metode Pembayaran</h4>
                    <p class="text-xs text-neutral-400 mb-3">Mendukung QRIS, GoPay, Transfer Bank BRI, dan Tunai di Kasir.</p>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded bg-neutral-800 text-[11px] font-mono text-neutral-300">QRIS</span>
                        <span class="px-2 py-1 rounded bg-neutral-800 text-[11px] font-mono text-neutral-300">BANK BRI</span>
                        <span class="px-2 py-1 rounded bg-neutral-800 text-[11px] font-mono text-neutral-300">CASH</span>
                    </div>
                </div>
                @else
                <div class="md:col-span-3 flex flex-col justify-center">
                    <h4 class="font-heading font-semibold text-white text-xs uppercase tracking-wider mb-3">Lokasi Kami</h4>
                    <div class="w-full h-32 bg-neutral-800 rounded-xl flex flex-col items-center justify-center border border-neutral-700/50">
                        <i data-lucide="map-pin" class="w-6 h-6 text-neutral-500 mb-2"></i>
                        <span class="text-xs text-neutral-500 font-medium">Dummy Map Placeholder</span>
                    </div>
                </div>
                @endif
            </div>

            <div class="pt-6 border-t border-neutral-800 flex flex-col sm:flex-row items-center justify-between text-xs text-neutral-500 gap-2">
                <p>&copy; {{ date('Y') }} Mobeli. Dikembangkan untuk Toko Kelontong Modern.</p>
                <div class="flex items-center gap-4">
                    <span>Privasi</span>
                    <span>Syarat & Ketentuan</span>
                    <span>Versi MVP 1.0</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
