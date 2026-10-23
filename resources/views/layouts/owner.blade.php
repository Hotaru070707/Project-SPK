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
        
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(1deg); }
        }
        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 6px rgba(16,185,129,0.5)); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 14px rgba(16,185,129,0.9)); }
        }
        @keyframes rotateOrbit {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes rotateOrbitReverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        @keyframes radarRipple {
            0% { transform: scale(0.6); opacity: 0.9; }
            100% { transform: scale(1.6); opacity: 0; }
        }
        .anim-float { animation: floatSlow 3.5s ease-in-out infinite; }
        .anim-pulse-glow { animation: pulseGlow 2.5s ease-in-out infinite; transform-origin: 100px 45px; }
        .anim-orbit { animation: rotateOrbit 12s linear infinite; transform-origin: 100px 45px; }
        .anim-orbit-reverse { animation: rotateOrbitReverse 16s linear infinite; transform-origin: 100px 45px; }
        .anim-radar-ripple { animation: radarRipple 2.4s cubic-bezier(0, 0.2, 0.8, 1) infinite; transform-origin: 100px 45px; }

        @keyframes beamFlow {
            0% { transform: translateY(-100%); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateY(260%); opacity: 0; }
        }
        .anim-beam-flow { animation: beamFlow 2.8s cubic-bezier(0.4, 0, 0.2, 1) infinite; }

        /* Sleek dark scrollbar for sidebar nav */
        aside nav::-webkit-scrollbar {
            width: 4px;
        }
        aside nav::-webkit-scrollbar-track {
            background: transparent;
        }
        aside nav::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        aside nav::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
    @include('partials.dark-mode-styles')
</head>
<body class="bg-neutral-50 text-neutral-800 font-sans antialiased min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800 h-screen sticky top-0">
        <!-- Logo -->
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800 shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-11 h-11 rounded-xl object-contain shadow-sm shrink-0">
            <div>
                <span class="font-heading font-extrabold text-white text-base tracking-tight block">Mobeli</span>
                <span class="text-[10px] text-slate-400">Admin & SPK Engine</span>
            </div>
        </div>

        <!-- Menus -->
        <nav class="flex-1 min-h-0 px-3.5 py-3 gap-2.5 overflow-y-auto text-xs flex flex-col">
            <div>
                <span class="px-2 text-[9.5px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Utama</span>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard Analitik
                    </a>
                </div>
            </div>

            <div>
                <span class="px-2 text-[9.5px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Sistem Pendukung Keputusan (TOPSIS)</span>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.topsis.criteria') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.topsis.criteria') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="sliders" class="w-3.5 h-3.5 text-emerald-400"></i> Kriteria & Bobot
                    </a>
                    <a href="{{ route('admin.topsis.calculate') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.topsis.calculate') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="calculator" class="w-3.5 h-3.5 text-emerald-400"></i> Matriks & Perhitungan
                    </a>
                    <a href="{{ route('admin.topsis.recommendations') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.topsis.recommendations') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-400"></i> Rekomendasi Restock Produk
                    </a>
                </div>
            </div>

            <div>
                <span class="px-2 text-[9.5px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Operasional Toko</span>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.products') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.products*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="package" class="w-3.5 h-3.5"></i> Produk & Stok Sembako
                    </a>
                    <a href="{{ route('admin.categories') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.categories*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="tags" class="w-3.5 h-3.5"></i> Kategori Produk
                    </a>
                    <a href="{{ route('admin.stock') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.stock*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i> Stok Masuk & Mutasi
                    </a>
                    <a href="{{ route('admin.orders') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.orders*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Pesanan & Verifikasi Bukti
                    </a>
                    <a href="{{ route('admin.reports') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.reports*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="chart-no-axes-combined" class="w-3.5 h-3.5"></i> Laporan
                    </a>
                    <a href="{{ route('admin.customers') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.customers*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i> Pelanggan & Member
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }}">
                        <i data-lucide="user-round-cog" class="w-3.5 h-3.5"></i> Akun Kasir
                    </a>
                </div>
            </div>

            <div>
                <span class="px-2 text-[9.5px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Pintasan Cepat</span>
                <div class="space-y-0.5">
                    <a href="{{ url('/kasir/pos') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-slate-800/80 text-emerald-300 hover:bg-slate-800 font-semibold">
                        <span class="flex items-center gap-2"><i data-lucide="monitor" class="w-3.5 h-3.5"></i> Buka POS Kasir</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
                    </a>
                </div>
            </div>

            <!-- SPK Live Dynamic Animation Card (Mengisi ruang kosong ke profil bawah) -->
            <div class="mt-auto pt-2 pb-0.5">
                <div class="relative overflow-hidden rounded-xl p-3 bg-gradient-to-b from-slate-800/90 via-slate-850/80 to-slate-900 border border-slate-700/70 shadow-lg group hover:border-emerald-500/50 transition-all duration-300">
                    <!-- Glow Background Ambience -->
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-500/15 rounded-full blur-xl pointer-events-none group-hover:bg-emerald-500/25 transition duration-500"></div>
                    <div class="absolute -bottom-8 -left-8 w-20 h-20 bg-blue-500/10 rounded-full blur-lg pointer-events-none"></div>

                    <!-- Animated Holographic SPK Radar Illustration -->
                    <div class="relative w-full h-16 mb-1 flex items-center justify-center overflow-hidden">
                        <svg viewBox="0 0 200 90" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <radialGradient id="spkCoreGlow" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#34d399" stop-opacity="1" />
                                    <stop offset="70%" stop-color="#059669" stop-opacity="0.8" />
                                    <stop offset="100%" stop-color="#047857" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="orbitGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.8" />
                                    <stop offset="50%" stop-color="#3b82f6" stop-opacity="0.5" />
                                    <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0.2" />
                                </linearGradient>
                            </defs>

                            <!-- Background Constellation Grid -->
                            <line x1="20" y1="45" x2="180" y2="45" stroke="#334155" stroke-width="0.5" stroke-dasharray="2,4" opacity="0.4" />
                            <line x1="100" y1="10" x2="100" y2="80" stroke="#334155" stroke-width="0.5" stroke-dasharray="2,4" opacity="0.4" />

                            <!-- Radar Ripple Waves -->
                            <circle cx="100" cy="45" r="18" fill="none" stroke="#10b981" stroke-width="1.2" class="anim-radar-ripple" opacity="0.7" />
                            <circle cx="100" cy="45" r="18" fill="none" stroke="#38bdf8" stroke-width="1" class="anim-radar-ripple" style="animation-delay: 1.1s;" opacity="0.5" />

                            <!-- Rotating Orbit Rings -->
                            <g class="anim-orbit">
                                <circle cx="100" cy="45" r="30" fill="none" stroke="url(#orbitGrad)" stroke-width="1" stroke-dasharray="4,4" />
                                <!-- Satellite 1 (C1: Urgensi Stok) -->
                                <circle cx="100" cy="15" r="4" fill="#10b981" />
                                <circle cx="100" cy="15" r="1.5" fill="#ffffff" />
                                <!-- Satellite 2 (C3: Lead Time) -->
                                <circle cx="100" cy="75" r="3.5" fill="#f59e0b" />
                                <circle cx="100" cy="75" r="1.5" fill="#ffffff" />
                            </g>

                            <g class="anim-orbit-reverse">
                                <circle cx="100" cy="45" r="22" fill="none" stroke="#475569" stroke-width="0.8" stroke-dasharray="3,3" opacity="0.6" />
                                <!-- Satellite 3 (C2: Kecepatan Jual) -->
                                <circle cx="122" cy="45" r="3.5" fill="#38bdf8" />
                                <circle cx="122" cy="45" r="1.5" fill="#ffffff" />
                                <!-- Satellite 4 (C4: Margin Laba) -->
                                <circle cx="78" cy="45" r="3" fill="#a855f7" />
                                <circle cx="78" cy="45" r="1" fill="#ffffff" />
                            </g>

                            <!-- Floating Badges -->
                            <g class="anim-float">
                                <rect x="14" y="16" width="36" height="15" rx="5" fill="#1e293b" stroke="#334155" stroke-width="0.8" />
                                <text x="32" y="27" font-size="7.5" font-weight="700" fill="#34d399" text-anchor="middle" font-family="Inter, sans-serif">TOPSIS</text>
                            </g>
                            <g class="anim-float" style="animation-delay: -1.8s;">
                                <rect x="150" y="58" width="36" height="15" rx="5" fill="#1e293b" stroke="#334155" stroke-width="0.8" />
                                <text x="168" y="69" font-size="7.5" font-weight="700" fill="#60a5fa" text-anchor="middle" font-family="JetBrains Mono, monospace">AI·SPK</text>
                            </g>

                            <!-- Center Glowing Core Node -->
                            <g class="anim-pulse-glow">
                                <circle cx="100" cy="45" r="11" fill="url(#spkCoreGlow)" />
                                <circle cx="100" cy="45" r="6" fill="#10b981" />
                                <path d="M 100 40 L 104 45 L 100 50 L 96 45 Z" fill="#ffffff" />
                            </g>
                        </svg>
                    </div>

                    <!-- Live Indicator & Title -->
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[9.5px] font-bold">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                            </span>
                            Engine Aktif
                        </span>
                        <span class="text-[9.5px] font-mono text-slate-400 font-semibold">4 Kriteria</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-white tracking-tight flex items-center gap-1">
                            <span>Sistem SPK Cerdas</span>
                            <span class="text-amber-400 text-[10px]">✨</span>
                        </h4>
                    </div>

                    <!-- Quick Action Button -->
                    <a href="{{ route('admin.topsis.recommendations') }}" class="group/btn mt-2 w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-[10px] transition-all duration-300 shadow-sm shadow-emerald-950/40 hover:scale-[1.02] active:scale-[0.98]">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-100"></i>
                            <span>Cek Rekomendasi</span>
                        </span>
                        <i data-lucide="arrow-right" class="w-3 h-3 text-emerald-100 group-hover/btn:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>

                <!-- Downward Animated Energy Stream towards Account Profile -->
                <div class="flex-1 min-h-[48px] flex flex-col items-center justify-center py-2 relative overflow-hidden group">
                    <!-- Central Track Line -->
                    <div class="w-0.5 h-full bg-gradient-to-b from-slate-700 via-emerald-500/40 to-slate-800 relative flex items-center justify-center">
                        <!-- Downward Gliding Pulse Beam -->
                        <div class="absolute w-1.5 h-10 -left-[2px] bg-gradient-to-b from-transparent via-emerald-400 to-transparent rounded-full anim-beam-flow blur-[0.5px]"></div>
                    </div>

                    <!-- Floating Interactive Live Status Pill -->
                    <div class="my-auto py-1 px-2.5 rounded-full bg-slate-800/95 border border-emerald-500/30 text-[9.5px] text-emerald-300 backdrop-blur-md shadow-md flex items-center gap-1.5 anim-float">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                        </span>
                        <span class="font-semibold tracking-tight">Sinkronisasi Toko</span>
                    </div>

                    <!-- Downward Pointer Arrow Pulse -->
                    <div class="w-1.5 h-1.5 rotate-45 border-b border-r border-emerald-400/80 mt-1 opacity-80 group-hover:opacity-100 transition animate-bounce"></div>
                </div>
            </div>
        </nav>

        <!-- User bottom -->
        <div class="p-3 border-t border-slate-800 flex items-center justify-between gap-2 shrink-0 bg-slate-900 z-10">
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
