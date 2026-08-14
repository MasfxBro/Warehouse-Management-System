<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WMS') - Warehouse Management System</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-slate-800 text-white flex-shrink-0 hidden md:flex flex-col transition-all duration-300">
            <!-- Logo / Brand -->
            <div class="h-16 flex items-center justify-center border-b border-slate-700 bg-slate-900">
                <h1 class="text-xl font-bold">WMS</h1>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto py-4">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white border-l-4 border-blue-500' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>

                <!-- Master Data Section -->
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <div class="px-6 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
                    Master Data
                </div>
                
                <a href="{{ route('master.barang.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('master.barang.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Master Barang
                </a>

                <a href="{{ route('master.supplier.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('master.supplier.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Supplier
                </a>

                <a href="{{ route('master.customer.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('master.customer.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Customer
                </a>

                <a href="{{ route('master.rack.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('master.rack.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                    Lokasi Rak
                </a>
                @endif

                <!-- Transaksi Section -->
                <div class="px-6 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
                    Transaksi
                </div>

                @if(auth()->check() && !auth()->user()->isOperator() || auth()->user()->role->value === 'operator')
                <a href="{{ route('inbound.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('inbound.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                    Inbound (Penerimaan)
                </a>
                @endif

                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->role->value === 'operator'))
                <a href="{{ route('outbound.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('outbound.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" transform="rotate(180 12 12)"></path>
                    </svg>
                    Outbound (Pengiriman)
                </a>
                @endif

                <!-- Inventory Section -->
                <div class="px-6 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
                    Inventory
                </div>

                <a href="{{ route('inventory.kartu-stok') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('inventory.kartu-stok*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Kartu Stok
                </a>

                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <a href="{{ route('inventory.stock-opname.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('inventory.stock-opname.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Stock Opname
                </a>
                @endif

                <!-- Laporan -->
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <div class="px-6 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
                    Laporan
                </div>

                <a href="{{ route('laporan.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('laporan.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Laporan
                </a>
                @endif

                <!-- User Management (Admin Only) -->
                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="px-6 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4">
                    Pengaturan
                </div>

                <a href="{{ route('users.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('users.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Kelola Pengguna
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shadow-sm">
                <div class="flex items-center">
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="md:hidden mr-4 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <h2 class="text-xl font-semibold text-gray-800">
                        @yield('page-title', 'Dashboard')
                    </h2>
                </div>

                <!-- User Info & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name ?? 'Guest' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role->label() ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="h-10 w-10 rounded-full bg-slate-600 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-red-600 transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <p class="font-semibold mb-2">Terdapat kesalahan:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

    <!-- Mobile Sidebar -->
    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-800 text-white z-50 transform -translate-x-full transition-transform duration-300 md:hidden">
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-700 bg-slate-900">
            <h1 class="text-xl font-bold">WMS</h1>
            <button id="close-mobile-menu" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Same navigation as desktop sidebar (copy from above) -->
    </aside>

    @stack('scripts')

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');
        const closeMobileMenu = document.getElementById('close-mobile-menu');

        mobileMenuButton?.addEventListener('click', () => {
            mobileSidebar.classList.remove('-translate-x-full');
            mobileOverlay.classList.remove('hidden');
        });

        [closeMobileMenu, mobileOverlay].forEach(el => {
            el?.addEventListener('click', () => {
                mobileSidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
            });
        });

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]').forEach(el => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
