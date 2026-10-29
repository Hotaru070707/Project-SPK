<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Mobeli - SPK Kelontong' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        sidebar: '#0f172a',
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
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .price-font { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
    </style>
    @include('partials.dark-mode-styles')
</head>
<body class="bg-neutral-50 text-neutral-800 font-sans antialiased min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800">
        <!-- Logo -->
        <div class="h-16 flex items-center gap-2.5 px-6 border-b border-slate-800">
            <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm">
            <div>
                <span class="font-heading font-extrabold text-white text-base tracking-tight block">Mobeli</span>
                <span class="text-[10px] text-slate-400">Admin & SPK Engine</span>
            </div>
        </div>

        <!-- Menus -->
        <nav class="flex-1 px-4 py-5 space-y-6 overflow-y-auto text-xs">
            <div>
                <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Utama</span>
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard Analitik
                    </a>
                </div>
            </div>

            <div>
                <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Sistem Pendukung Keputusan (TOPSIS)</span>
                <div class="space-y-1">
                    <a href="{{ route('admin.topsis.criteria') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.topsis.criteria') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="sliders" class="w-4 h-4 text-emerald-400"></i> Kriteria & Bobot
                    </a>
                    <a href="{{ route('admin.topsis.calculate') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.topsis.calculate') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="calculator" class="w-4 h-4 text-emerald-400"></i> Matriks & Perhitungan
                    </a>
                    <a href="{{ route('admin.topsis.recommendations') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.topsis.recommendations') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="sparkles" class="w-4 h-4 text-emerald-400"></i> Rekomendasi Restock Produk
                    </a>
                    <a href="{{ route('admin.topsis.history') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.topsis.history*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-400"></i> Riwayat Perhitungan
                    </a>
                </div>
            </div>

            <div>
                <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Operasional Toko</span>
                <div class="space-y-1">
                    <a href="{{ route('admin.products') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.products*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="package" class="w-4 h-4"></i> Produk & Stok Sembako
                    </a>
                    <a href="{{ route('admin.categories') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.categories*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="tags" class="w-4 h-4"></i> Kategori Produk
                    </a>
                    <a href="{{ route('admin.stock') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.stock*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="boxes" class="w-4 h-4"></i> Stok Masuk & Mutasi
                    </a>
                    <a href="{{ route('admin.orders') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.orders*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i> Pesanan & Verifikasi Bukti
                    </a>
                    <a href="{{ route('admin.reports') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.reports*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="chart-no-axes-combined" class="w-4 h-4"></i> Laporan
                    </a>
                    <a href="{{ route('admin.customers') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.customers*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="users" class="w-4 h-4"></i> Pelanggan & Member
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ request()->routeIs('admin.users*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="user-round-cog" class="w-4 h-4"></i> Akun Kasir
                    </a>
                </div>
            </div>

            <div>
                <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Pintasan Cepat</span>
                <div class="space-y-1">
                    <a href="{{ url('/kasir/pos') }}" class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-800/80 text-emerald-300 hover:bg-slate-800 font-semibold">
                        <span class="flex items-center gap-2"><i data-lucide="monitor" class="w-4 h-4"></i> Buka POS Kasir</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
                    </a>
                </div>
            </div>
        </nav>

        <!-- User bottom -->
        <div class="p-3 border-t border-slate-800 flex items-center justify-between gap-2">
            <a href="{{ route('profile.edit') }}" title="Buka Profil Saya" class="flex items-center gap-2.5 p-1.5 -m-1.5 rounded-xl hover:bg-slate-800/80 transition flex-1 min-w-0 group">
                @if(auth()->user()?->avatar)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-emerald-500/50">
                @else
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition">
                        {{ substr(auth()->user()?->name ?? 'Admin', 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate group-hover:text-emerald-300 transition">{{ auth()->user()?->name ?? 'Admin' }}</span>
                    <span class="text-[10px] text-emerald-400 font-mono block">Owner/Admin</span>
                </div>
            </a>
            <button type="button" onclick="openLogoutModal(event)" title="Keluar Akun" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded transition shrink-0">
                <i data-lucide="log-out" class="w-4 h-4"></i>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top bar -->
        <header class="h-16 bg-white border-b border-neutral-200 px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="window.history.back()" class="p-2 -ml-2 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-lg transition" title="Kembali">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </button>
                <h2 class="font-heading font-bold text-lg text-neutral-900 tracking-tight">
                    {{ $header ?? 'Panel Pengelolaan Mobeli' }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <!-- Detail Hari, Tanggal, Jam WIB (Ringkas & Rapi) -->
                <div class="hidden lg:flex items-center gap-2.5 px-3 py-1 rounded-xl bg-neutral-100/90 dark:bg-slate-800/90 border border-neutral-200/80 dark:border-slate-700/80 text-xs shadow-2xs">
                    <div class="w-6 h-6 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="flex flex-col text-left leading-tight">
                        <span id="currentTimeDisplay" class="font-mono font-bold text-xs text-neutral-900 dark:text-slate-100 tracking-tight">10:48:00 WIB</span>
                        <span id="currentDateDisplay" class="text-[10px] text-neutral-500 dark:text-slate-400 font-medium">Senin, 05 Okt</span>
                    </div>
                </div>

                <!-- Dark / Light Mode Toggle Button -->
                <button type="button" onclick="toggleTheme()" title="Ganti Mode Gelap / Terang" class="p-2.5 rounded-full border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-neutral-600 dark:text-amber-400 hover:bg-neutral-100 dark:hover:bg-slate-700 transition shadow-sm">
                    <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                </button>

                <!-- Tombol Profil Saya (Diperbesar) -->
                <a href="{{ route('profile.edit') }}" title="Buka Profil Saya" class="flex items-center gap-2.5 px-4 py-2 rounded-full border border-neutral-200 dark:border-slate-700 hover:border-emerald-500 text-neutral-800 dark:text-slate-100 hover:text-emerald-600 dark:hover:text-emerald-400 font-bold text-sm bg-white dark:bg-slate-800 hover:bg-emerald-50/60 dark:hover:bg-slate-700 transition shadow-sm group">
                    @if(auth()->user()?->avatar)
                        <img src="{{ auth()->user()->avatar_url }}" class="w-7 h-7 rounded-full object-cover ring-2 ring-emerald-500/40 shrink-0">
                    @else
                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition">
                            {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
                        </div>
                    @endif
                    <span class="leading-none">Profil Saya</span>
                </a>
            </div>
        </header>

        @include('partials.flash-toast')
        @include('partials.logout-modal')

        <main class="p-6">
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Dark/Light Mode Toggle
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

            const dateEl = document.getElementById('currentDateDisplay');
            const timeEl = document.getElementById('currentTimeDisplay');
            if (dateEl) dateEl.textContent = dateStr;
            if (timeEl) timeEl.textContent = timeStr;
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
    @stack('scripts')
</body>
</html>
