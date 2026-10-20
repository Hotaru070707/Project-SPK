<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kasir POS - Mobeli' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    
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
                        darkpos: '#0f172a',
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

        /* Global Dark Mode Overrides */
        html.dark {
            color-scheme: dark;
        }
        html.dark body {
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }
        html.dark .bg-white {
            background-color: #1e293b !important;
        }
        html.dark .bg-slate-100 {
            background-color: #0b1120 !important;
        }
        html.dark .border-slate-200, html.dark .border-neutral-200 {
            border-color: #334155 !important;
        }
        html.dark .text-slate-800, html.dark .text-neutral-800 {
            color: #f8fafc !important;
        }
        html.dark .text-slate-600, html.dark .text-neutral-600 {
            color: #cbd5e1 !important;
        }
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Top Cashier Nav -->
    <header class="bg-slate-900 text-white px-4 sm:px-6 py-2.5 flex items-center justify-between border-b border-slate-800 shadow-md">
        <div class="flex items-center gap-4">
            <a href="{{ url('/kasir') }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('images/logo.png') }}" alt="Mobeli Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm group-hover:scale-105 transition">
                <span class="font-heading font-extrabold text-base tracking-tight text-white">
                    Mobeli <span class="text-xs font-normal text-slate-400">Kasir</span>
                </span>
            </a>

            <div class="hidden sm:flex items-center gap-1 text-xs">
                <a href="{{ route('cashier.pos') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('cashier.pos') ? 'bg-emerald-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Kasir Transaksi (F1)
                </a>
                <a href="{{ route('cashier.transactions') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('cashier.transactions') ? 'bg-emerald-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Riwayat Transaksi
                </a>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs">
            <!-- Tombol Kembali -->
            <button onclick="window.history.back()" class="text-xs flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded transition" title="Kembali">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Kembali</span>
            </button>
            
            <a href="{{ route('profile.edit') }}" title="Buka Profil Saya" class="flex items-center gap-2 text-right group hover:opacity-80 transition">
                @if(auth()->user()?->avatar)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-6 h-6 rounded-full object-cover ring-1 ring-emerald-500/50">
                @else
                    <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-[10px]">
                        {{ substr(auth()->user()?->name ?? 'K', 0, 1) }}
                    </div>
                @endif
                <div class="text-right hidden sm:block">
                    <span class="font-bold text-white group-hover:text-emerald-300 transition block leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] text-emerald-400 font-mono block">Kasir</span>
                </div>
            </a>
            <!-- Theme Toggle Button -->
            <button type="button" onclick="toggleTheme()" title="Ganti Mode Gelap / Terang" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 dark:text-amber-400 transition">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
            </button>

            <a href="{{ url('/') }}" class="text-xs text-slate-300 hover:text-white px-2 py-1 rounded bg-slate-800">
                Toko Online
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-2.5 py-1 bg-red-600/80 hover:bg-red-600 text-white font-bold rounded">
                    Keluar
                </button>
            </form>
        </div>
    </header>

    @include('partials.flash-toast')

    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

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
