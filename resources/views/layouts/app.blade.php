<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mobeli - Toko Kelontong Modern' }}</title>
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
                            DEFAULT: '#2563eb',
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        emerald: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                        secondary: {
                            DEFAULT: '#f97316',
                            500: '#f97316',
                            600: '#ea580c',
                        },
                        ivory: '#f8fbff',
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
            background-color: #f8fbff;
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
    </style>
    @include('partials.dark-mode-styles')
</head>
<body class="min-h-screen flex flex-col antialiased">
    <!-- Main Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-18 flex flex-wrap md:flex-nowrap items-center justify-between gap-x-4 gap-y-2 py-2">
            <!-- Brand Logo -->
            <div class="flex items-center gap-2 sm:gap-3">
                <button onclick="window.history.back()" class="p-2 -ml-2 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-lg transition" title="Kembali">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </button>
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-11 h-11 rounded-xl object-contain shadow-sm group-hover:scale-105 transition">
                <div>
                    <span class="font-heading font-extrabold text-xl text-neutral-900 tracking-tight flex items-center gap-1.5">
                        Mobeli
                    </span>
                    <p class="text-[11px] text-neutral-500 leading-tight">Kelontong & Keputusan Cerdas</p>
                </div>
            </a>
            </div>

            <nav aria-label="Navigasi toko" class="order-3 flex w-full items-center gap-4 overflow-x-auto whitespace-nowrap border-t border-neutral-100 pt-2 text-sm md:order-none md:w-auto md:border-t-0 md:pt-0">
                <a href="{{ route('home') }}" class="font-medium text-neutral-700 hover:text-primary transition">Beranda</a>
                <a href="{{ route('about') }}" class="font-medium text-neutral-700 hover:text-primary transition">Tentang Kami</a>
                <a href="{{ route('contact') }}" class="font-medium text-neutral-700 hover:text-primary transition">Hubungi Kami</a>
            </nav>

            <!-- Nav Actions & User Info -->
            <div class="flex items-center gap-3">
                <!-- Detail Hari, Tanggal & Jam Live Widget (Ringkas & Rapi) -->
                <div class="hidden lg:flex items-center gap-2.5 px-3 py-1 rounded-xl bg-neutral-100/90 dark:bg-slate-800/90 border border-neutral-200/80 dark:border-slate-700/80 text-xs shadow-2xs">
                    <div class="w-6 h-6 rounded-lg bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary shrink-0">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="flex flex-col text-left leading-tight">
                        <span id="currentTimeDisplay" class="font-mono font-bold text-xs text-neutral-900 dark:text-slate-100 tracking-tight">11:35:43 WIB</span>
                        <span id="currentDateDisplay" class="text-[10px] text-neutral-500 dark:text-slate-400 font-medium">Kamis, 22 Okt</span>
                    </div>
                </div>

                <!-- Theme Toggle -->
                <button type="button" onclick="toggleTheme()" title="Ganti Mode Gelap / Terang" class="p-2 rounded-lg text-neutral-600 dark:text-amber-400 hover:bg-neutral-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                </button>

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

                    @if(!auth()->user()->isOwner())
                        <a href="{{ url('/akun/keranjang') }}" id="nav-cart-btn" class="relative p-2 rounded-lg text-neutral-700 dark:text-slate-300 hover:bg-neutral-100 dark:hover:bg-slate-800 transition">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                            <span id="cart-counter" class="absolute -top-1 -right-1 bg-secondary text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                                {{ session('cart_count', 0) }}
                            </span>
                        </a>
                    @endif

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
                        <button type="button" onclick="openLogoutModal(event)" title="Keluar Akun" class="p-2 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 dark:hover:text-red-400 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
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

    @include('partials.flash-toast')
    @include('partials.cart-animation')
    @include('partials.logout-modal')

    <!-- Main Content Slot -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-neutral-900 text-neutral-300 pt-12 pb-8 border-t border-neutral-800 mt-16 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-white font-heading font-bold text-lg">
                        <span class="w-7 h-7 rounded-lg bg-primary text-white flex items-center justify-center text-sm font-extrabold">M</span>
                        Mobeli
                    </div>
                    @if(!request()->is('login', 'daftar', 'masuk'))
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Toko kelontong modern untuk kebutuhan harian dengan layanan belanja online dan kasir yang praktis.
                    </p>
                    @endif
                    <div class="text-xs text-neutral-400">
                        📍 Jl. Merdeka No. 45, Sukajadi<br>
                        📞 WhatsApp: 0812-3456-7890
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <h4 class="font-heading font-semibold text-white text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-primary"></i> Lokasi Toko Fisik
                        </h4>
                        <a href="https://maps.google.com/?q=Sukajadi+Bandung" target="_blank" class="text-[11px] text-primary hover:text-blue-400 flex items-center gap-1 font-medium transition">
                            Buka Peta <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                    </div>
                    
                    <div class="relative w-full h-32 rounded-2xl overflow-hidden border border-neutral-700/60 bg-neutral-800/80 group flex flex-col justify-between p-3.5 shadow-sm">
                        <!-- Stylized Map Pattern Background -->
                        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#64748b 1px, transparent 1px); background-size: 14px 14px;"></div>
                        
                        <!-- Map Grid Lines Simulation -->
                        <div class="absolute inset-0 opacity-15 pointer-events-none">
                            <div class="absolute top-1/2 left-0 right-0 h-1 bg-neutral-400 -rotate-3"></div>
                            <div class="absolute top-0 bottom-0 left-1/3 w-1 bg-neutral-400 rotate-6"></div>
                            <div class="absolute top-0 bottom-0 right-1/4 w-1 bg-neutral-500 -rotate-12"></div>
                        </div>

                        <!-- Card Content -->
                        <div class="relative z-10 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-primary/20 border border-primary/40 flex items-center justify-center text-primary shrink-0 shadow-sm">
                                <i data-lucide="store" class="w-4 h-4 text-primary"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-white tracking-tight">Toko Kelontong Mobeli</h5>
                                <p class="text-[11px] text-neutral-400 mt-0.5">Jl. Merdeka No. 45, Sukajadi, Kota Bandung</p>
                            </div>
                        </div>

                        <div class="relative z-10 flex items-center justify-between text-[10px] text-neutral-400 border-t border-neutral-700/50 pt-2 mt-auto">
                            <span>🕒 Buka: 07:00 - 21:30 WIB</span>
                            <span class="text-emerald-400 font-semibold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Buka Hari Ini
                            </span>
                        </div>
                    </div>
                </div>
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

        // Live Clock & Date Widget (Format Hari, Tanggal, Jam WIB)
        function updateDateTime() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            const dayName = days[now.getDay()];
            const date = String(now.getDate()).padStart(2, '0');
            const month = months[now.getMonth()];
            const year = now.getFullYear();

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            const dateStr = `${dayName}, ${date} ${month}`;
            const timeStr = `${hours}:${minutes}:${seconds} WIB`;

            const dateElem = document.getElementById('currentDateDisplay');
            const timeElem = document.getElementById('currentTimeDisplay');
            if (dateElem) dateElem.textContent = dateStr;
            if (timeElem) timeElem.textContent = timeStr;
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
    @stack('scripts')
</body>
</html>
