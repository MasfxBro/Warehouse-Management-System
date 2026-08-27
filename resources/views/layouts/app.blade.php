<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WMS Prototipe 2') — Warehouse Management System</title>

    <!-- Font Awesome 6 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- QRCode.js -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#f7f9fb] text-[#191c1e] antialiased flex flex-col font-sans">

<div class="flex h-screen overflow-hidden">

    <!-- ============================================================
         SIDEBAR — Fixed 240px
         ============================================================ -->
    <aside class="w-[240px] bg-white border-r border-[#e2e8f0] flex flex-col flex-shrink-0 z-20">

        <!-- Logo / Brand -->
        <div class="h-14 flex items-center px-4 border-b border-[#e2e8f0] gap-3">
            <div class="sidebar-logo-box">
                <i class="fa-solid fa-warehouse text-xs"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[13px] font-bold text-slate-900 leading-tight tracking-tight">WMS Prototipe 2</div>
                <div class="text-[10px] text-slate-400 font-medium">Warehouse Management</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5 pb-4">

            <!-- Navigasi Utama -->
            <p class="sidebar-section-label">Navigasi Utama</p>

            @php $isDashboard = request()->routeIs('dashboard'); @endphp
            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ $isDashboard ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high {{ $isDashboard ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Dashboard</span>
            </a>

            <!-- Master Data -->
            <p class="sidebar-section-label">Master Data</p>

            @php $isBarang = request()->routeIs('master.barang.*'); @endphp
            <a href="{{ route('master.barang.index') }}"
               class="sidebar-link {{ $isBarang ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked {{ $isBarang ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Data Barang</span>
            </a>

            @php $isRak = request()->routeIs('master.rak.*'); @endphp
            <a href="{{ route('master.rak.index') }}"
               class="sidebar-link {{ $isRak ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group {{ $isRak ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Lokasi Rak</span>
            </a>

            @php $isSupplier = request()->routeIs('master.supplier.*'); @endphp
            <a href="{{ route('master.supplier.index') }}"
               class="sidebar-link {{ $isSupplier ? 'active' : '' }}">
                <i class="fa-solid fa-building {{ $isSupplier ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Data Supplier</span>
            </a>

            @php $isCustomer = request()->routeIs('master.customer.*'); @endphp
            <a href="{{ route('master.customer.index') }}"
               class="sidebar-link {{ $isCustomer ? 'active' : '' }}">
                <i class="fa-solid fa-users {{ $isCustomer ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Data Customer</span>
            </a>

            <!-- Transaksi -->
            <p class="sidebar-section-label">Transaksi</p>

            @php $isInbound = request()->routeIs('inbound.*'); @endphp
            <a href="{{ route('inbound.index') }}"
               class="sidebar-link {{ $isInbound ? 'active' : '' }}">
                <i class="fa-solid fa-arrow-down-to-bracket {{ $isInbound ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Inbound (Masuk)</span>
            </a>

            @php $isOutbound = request()->routeIs('outbound.*'); @endphp
            <a href="{{ route('outbound.index') }}"
               class="sidebar-link {{ $isOutbound ? 'active' : '' }}">
                <i class="fa-solid fa-arrow-up-from-bracket {{ $isOutbound ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Outbound (Keluar)</span>
            </a>

            <!-- Inventory -->
            <p class="sidebar-section-label">Inventory</p>

            @php $isKartuStok = request()->routeIs('inventory.kartu-stok.*'); @endphp
            <a href="{{ route('inventory.kartu-stok.index') }}"
               class="sidebar-link {{ $isKartuStok ? 'active' : '' }}">
                <i class="fa-solid fa-rectangle-list {{ $isKartuStok ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Kartu Stok</span>
            </a>

            @php $isOpname = request()->routeIs('inventory.stock-opname.*'); @endphp
            <a href="{{ route('inventory.stock-opname.index') }}"
               class="sidebar-link {{ $isOpname ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check {{ $isOpname ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Stock Opname</span>
            </a>

            <!-- Laporan -->
            <p class="sidebar-section-label">Laporan</p>

            @php $isLaporan = request()->routeIs('laporan.*'); @endphp
            <a href="{{ route('laporan.index') }}"
               class="sidebar-link {{ $isLaporan ? 'active' : '' }}">
                <i class="fa-solid fa-chart-bar {{ $isLaporan ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                <span>Laporan & Export</span>
            </a>

            <!-- Sistem — Admin Only -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <p class="sidebar-section-label">Sistem</p>

                @php $isLogs = request()->routeIs('logs.*'); @endphp
                <a href="{{ route('logs.index') }}"
                   class="sidebar-link {{ $isLogs ? 'active' : '' }}">
                    <i class="fa-solid fa-scroll {{ $isLogs ? 'text-[#0058be]' : 'text-slate-400' }}"></i>
                    <span>Log Activity</span>
                </a>
            @endif

        </nav>

        <!-- User Footer -->
        <div class="px-3 pt-4 pb-4 border-t border-[#e2e8f0] bg-[#f7f9fb]">
            <!-- User Info -->
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex flex-col min-w-0 flex-1 gap-0.5">
                    <span class="text-[12px] font-semibold text-slate-800 truncate leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] text-slate-400 capitalize truncate">{{ auth()->user()->role->label() }}</span>
                </div>
            </div>
            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg
                               text-xs font-semibold text-red-600 hover:text-red-700
                               bg-white hover:bg-red-50 border border-red-200 hover:border-red-300
                               transition-colors cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    Keluar dari Sistem
                </button>
            </form>
        </div>
    </aside>

    <!-- ============================================================
         MAIN CONTAINER
         ============================================================ -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- TOPBAR -->
        <header class="h-14 bg-white border-b border-[#e2e8f0] flex items-center justify-between px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <!-- Breadcrumb page title -->
                <h1 class="topbar-heading">@yield('page_heading', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2">

                <!-- Role Badge -->
                @if(auth()->user()->isAdmin())
                    <span class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-[11px] font-semibold px-2.5 py-1 rounded-md">
                        <i class="fa-solid fa-chalkboard-user text-[10px]"></i>
                        Guru (Admin)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-[#0058be] border border-blue-200 text-[11px] font-semibold px-2.5 py-1 rounded-md">
                        <i class="fa-solid fa-graduation-cap text-[10px]"></i>
                        Operator (Siswa)
                    </span>
                @endif

                <!-- Student Identity Display -->
                @if(auth()->user()->isUser() && session()->has('student_identity'))
                    @php $student = session('student_identity'); @endphp
                    <div class="hidden sm:flex items-center gap-1.5 bg-[#f2f4f6] border border-[#e2e8f0] px-3 py-1 rounded-md text-[11px] text-slate-700">
                        <i class="fa-solid fa-user-tag text-slate-400 text-[10px]"></i>
                        <span class="font-bold text-slate-900">{{ $student['name'] }}</span>
                        <span class="text-slate-400">·</span>
                        <span class="font-semibold text-[#0058be]">{{ $student['class'] }}</span>
                    </div>
                    <form action="{{ route('student-identity.reset') }}" method="POST">
                        @csrf
                        <button type="submit" title="Ganti Identitas"
                                class="inline-flex items-center gap-1.5 text-[11px] text-slate-500 hover:text-[#0058be] bg-[#f2f4f6] hover:bg-blue-50 border border-[#e2e8f0] hover:border-blue-200 px-2.5 py-1 rounded-md transition-colors cursor-pointer">
                            <i class="fa-solid fa-rotate text-[10px]"></i>
                            Ganti Identitas
                        </button>
                    </form>
                @endif

            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="flex-1 overflow-y-auto p-6 bg-[#f7f9fb]">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert-success mb-5">
                    <i class="fa-solid fa-circle-check text-base flex-shrink-0 mt-0.5"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error mb-5">
                    <i class="fa-solid fa-triangle-exclamation text-base flex-shrink-0 mt-0.5"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @if(session('info'))
                <div class="alert-info mb-5">
                    <i class="fa-solid fa-circle-info text-base flex-shrink-0 mt-0.5"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<!-- ================================================================
     STUDENT IDENTITY MODAL (Non-bypassable)
     ================================================================ -->
@if(auth()->check() && auth()->user()->isUser() && (!session()->has('student_identity') || !empty($require_student_identity_modal)))
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="max-w-md w-full bg-white rounded-xl shadow-2xl border border-[#e2e8f0] overflow-hidden">
            <!-- Header -->
            <div class="bg-slate-900 px-6 py-6 text-white text-center">
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-graduation-cap text-2xl"></i>
                </div>
                <h2 class="text-lg font-bold tracking-tight">Form Identitas Siswa</h2>
                <p class="text-[11px] text-slate-400 mt-1">Wajib diisi sebelum memulai sesi praktikum WMS</p>
            </div>
            <!-- Body -->
            <form action="{{ route('student-identity.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="wms-label">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Elmaliq Akbar" class="wms-input">
                </div>
                <div>
                    <label class="wms-label">Kelas <span class="text-red-500">*</span></label>
                    <input type="text" name="class" required placeholder="Contoh: XII RPL 1" class="wms-input">
                </div>
                <div>
                    <label class="wms-label">NIS (Nomor Induk Siswa) <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" required placeholder="Contoh: 202612345" class="wms-input font-mono">
                </div>
                <div class="pt-1">
                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-[#0058be] hover:bg-[#004499] text-white font-bold text-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-play text-xs"></i>
                        Mulai Praktikum
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 text-center">
                    Data identitas ini dicatat otomatis dalam System Activity Log.
                </p>
            </form>
        </div>
    </div>
@endif

</body>
</html>
