<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WMS Prototipe 2') - Warehouse Management System</title>

    <!-- Disable browser speculative prefetching (Chrome, Edge) -->
    <meta http-equiv="x-dns-prefetch-control" content="off">
    <meta name="referrer" content="no-referrer">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- QRCode.js -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-surface text-on-surface antialiased flex flex-col font-sans">

<div class="flex h-screen overflow-hidden">

    <!-- ============================================================
         SIDEBAR — Fixed 240px, collapsible
         ============================================================ -->
    <aside id="main-sidebar" class="w-60 bg-white border-r border-[#e2e8f0] flex flex-col shrink-0 z-20 transition-all duration-300 ease-in-out">

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
                <i class="fa-solid fa-gauge-high {{ $isDashboard ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Dashboard</span>
            </a>

            <!-- Master Data -->
            <p class="sidebar-section-label">Master Data</p>

            @php $isBarang = request()->routeIs('master.barang.*'); @endphp
            <a href="{{ route('master.barang.index') }}"
               class="sidebar-link {{ $isBarang ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked {{ $isBarang ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Data Barang</span>
            </a>

            @php $isRak = request()->routeIs('master.rak.*'); @endphp
            <a href="{{ route('master.rak.index') }}"
               class="sidebar-link {{ $isRak ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group {{ $isRak ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Lokasi Rak</span>
            </a>

            @php $isSupplier = request()->routeIs('master.supplier.*'); @endphp
            <a href="{{ route('master.supplier.index') }}"
               class="sidebar-link {{ $isSupplier ? 'active' : '' }}">
                <i class="fa-solid fa-building {{ $isSupplier ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Data Supplier</span>
            </a>

            @php $isCustomer = request()->routeIs('master.customer.*'); @endphp
            <a href="{{ route('master.customer.index') }}"
               class="sidebar-link {{ $isCustomer ? 'active' : '' }}">
                <i class="fa-solid fa-users {{ $isCustomer ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Data Customer</span>
            </a>

            <!-- Transaksi -->
            <p class="sidebar-section-label">Transaksi</p>

            @php $isInbound = request()->routeIs('inbound.*'); @endphp
            <a href="{{ route('inbound.index') }}"
               class="sidebar-link {{ $isInbound ? 'active' : '' }}">
                <i class="fa-solid fa-truck-ramp-box {{ $isInbound ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Inbound (Masuk)</span>
            </a>

            @php $isOutbound = request()->routeIs('outbound.*'); @endphp
            <a href="{{ route('outbound.index') }}"
               class="sidebar-link {{ $isOutbound ? 'active' : '' }}">
                <i class="fa-solid fa-arrow-up-from-bracket {{ $isOutbound ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Outbound (Keluar)</span>
            </a>

            <!-- Inventory -->
            <p class="sidebar-section-label">Inventory</p>

            @if(auth()->user()->isAdmin())
                @php $isKartuStok = request()->routeIs('inventory.kartu-stok.*'); @endphp
                <a href="{{ route('inventory.kartu-stok.index') }}"
                   class="sidebar-link {{ $isKartuStok ? 'active' : '' }}">
                    <i class="fa-solid fa-rectangle-list {{ $isKartuStok ? 'text-secondary' : 'text-slate-400' }}"></i>
                    <span>Kartu Stok</span>
                </a>
            @endif

            @php $isOpname = request()->routeIs('inventory.stock-opname.*'); @endphp
            <a href="{{ route('inventory.stock-opname.index') }}"
               class="sidebar-link {{ $isOpname ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check {{ $isOpname ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Stock Opname</span>
            </a>

            <!-- Laporan -->
            <p class="sidebar-section-label">Laporan</p>

            @php $isLaporan = request()->routeIs('laporan.*'); @endphp
            <a href="{{ route('laporan.index') }}"
               class="sidebar-link {{ $isLaporan ? 'active' : '' }}">
                <i class="fa-solid fa-chart-bar {{ $isLaporan ? 'text-secondary' : 'text-slate-400' }}"></i>
                <span>Laporan & Export</span>
            </a>

            <!-- Sistem — Admin Only -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <p class="sidebar-section-label">Sistem</p>

                @php $isLogs = request()->routeIs('logs.*'); @endphp
                <a href="{{ route('logs.index') }}"
                   class="sidebar-link {{ $isLogs ? 'active' : '' }}">
                    <i class="fa-solid fa-scroll {{ $isLogs ? 'text-secondary' : 'text-slate-400' }}"></i>
                    <span>Log Activity</span>
                </a>
            @endif

        </nav>

        <!-- User Footer -->
        <div class="px-3 pt-4 pb-4 border-t border-[#e2e8f0] bg-surface">
            <!-- User Info -->
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
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
        <header class="h-14 bg-white border-b border-[#e2e8f0] flex items-center justify-between px-6 z-10 shrink-0">
            <div class="flex items-center gap-3">
                <!-- Hamburger toggle sidebar -->
                <button id="sidebar-toggle" type="button"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-surface-low transition-colors cursor-pointer"
                        title="Toggle Sidebar">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
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
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-secondary border border-blue-200 text-[11px] font-semibold px-2.5 py-1 rounded-md">
                        <i class="fa-solid fa-graduation-cap text-[10px]"></i>
                        Operator (Siswa)
                    </span>
                @endif

                <!-- Student Identity Display (read-only, no reset button) -->
                @if(auth()->user()->isUser() && session()->has('student_identity'))
                    @php $student = session('student_identity'); @endphp
                    <div class="hidden sm:flex items-center gap-1.5 bg-surface-low border border-[#e2e8f0] px-3 py-1 rounded-md text-[11px] text-slate-700">
                        <i class="fa-solid fa-user-tag text-slate-400 text-[10px]"></i>
                        <span class="font-bold text-slate-900">{{ $student['name'] }}</span>
                        <span class="text-slate-400">·</span>
                        <span class="font-semibold text-secondary">{{ $student['class'] }}</span>
                    </div>
                @endif

            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="flex-1 overflow-y-auto p-6 bg-surface">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert-success mb-5">
                    <i class="fa-solid fa-circle-check text-base shrink-0 mt-0.5"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error mb-5">
                    <i class="fa-solid fa-triangle-exclamation text-base shrink-0 mt-0.5"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @if(session('info'))
                <div class="alert-info mb-5">
                    <i class="fa-solid fa-circle-info text-base shrink-0 mt-0.5"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<!-- ================================================================
     STUDENT IDENTITY MODAL (Non-bypassable)
     ================================================================ -->@if(auth()->check() && auth()->user()->isUser() && (!session()->has('student_identity') || !empty($require_student_identity_modal)))
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="modal-overlay flex items-center justify-center">
        <div class="modal-box">
            <!-- Header -->
            <div class="modal-header border-b-0 flex-col text-center py-5 bg-slate-900 text-white rounded-t-xl">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>
                <h2 class="text-sm font-bold tracking-tight text-white">Form Identitas Siswa</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Wajib diisi sebelum memulai pendataan dan pencatatan!</p>
            </div>
            <!-- Body -->
            <form action="{{ route('student-identity.store') }}" method="POST"
                    class="modal-body" id="student-identity-form" novalidate>
                @csrf
                @if($errors->any())
                    <div class="flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 rounded-lg px-3.5 py-3 text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-sm shrink-0 mt-0.5"></i>
                        <div class="space-y-0.5">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label class="wms-label">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                        placeholder="Contoh: Elmaliq Akbar"
                        class="wms-input @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}
                        </p>
                    @enderror
                    <p id="err-name-identity" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Nama lengkap wajib diisi.
                    </p>
                        <p id="err-name-identity" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Nama lengkap wajib diisi.
                        </p>
                </div>

                <div>
                    <label class="wms-label">Kelas <span class="text-red-500">*</span></label>
                    <input type="text" name="class" required value="{{ old('class') }}"
                        placeholder="Contoh: XII RPL 1"
                        class="wms-input @error('class') border-red-400 @enderror">
                    @error('class')
                        <p class="flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}
                        </p>
                    @enderror
                    <p id="err-class-identity" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Kelas wajib diisi.
                    </p>
                        <p id="err-class-identity" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Kelas wajib diisi.
                        </p>
                </div>

                <div>
                    <label class="wms-label">NIS (Nomor Induk Siswa) <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" id="nis-input" required value="{{ old('nis') }}"
                        placeholder="Contoh: 202612345" inputmode="numeric" autocomplete="off"
                        class="wms-input font-mono @error('nis') border-red-400 @enderror">
                    <p id="nis-inline-error" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        NIS hanya boleh berisi angka (0-9).
                    </p>
                    @error('nis')
                        <p class="flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}
                        </p>
                    @else
                        <p class="text-[10px] text-slate-400 mt-1">Hanya boleh diisi dengan angka.</p>
                    @enderror
                </div>

                <div class="modal-footer flex-col gap-2">
                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-[#0058be] hover:bg-[#004499] text-white font-bold text-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-play text-xs"></i>
                        Mulai Praktikum
                    </button>
                    <p class="text-[11px] text-slate-400 text-center">
                        Data identitas ini dicatat otomatis dalam System Activity Log.
                    </p>
                </div>
            </form>
        </div>
    </div>
        <script>
        (function () {
            var form     = document.getElementById('student-identity-form');
            var nisInput = document.getElementById('nis-input');
            if (!form) return;

            function showFieldErr(el, errId) {
                if (el) el.classList.add('border-red-400');
                var p = document.getElementById(errId);
                if (p) { p.classList.remove('hidden'); p.classList.add('flex'); }
            }
            function hideFieldErr(el, errId) {
                if (el) el.classList.remove('border-red-400');
                var p = document.getElementById(errId);
                if (p) { p.classList.add('hidden'); p.classList.remove('flex'); }
            }

            var nameEl  = form.querySelector('[name="name"]');
            var classEl = form.querySelector('[name="class"]');

            if (nameEl)  nameEl.addEventListener('input',  function () { hideFieldErr(nameEl,  'err-name-identity'); });
            if (classEl) classEl.addEventListener('input', function () { hideFieldErr(classEl, 'err-class-identity'); });

            if (nisInput) {
                nisInput.addEventListener('input', function () {
                    var clean = this.value.replace(/[^0-9]/g, '');
                    if (this.value !== clean) { this.value = clean; showFieldErr(nisInput, 'nis-inline-error'); }
                    else { hideFieldErr(nisInput, 'nis-inline-error'); }
                });
                nisInput.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var pasted = (e.clipboardData || window.clipboardData).getData('text');
                    this.value = pasted.replace(/[^0-9]/g, '');
                    this.dispatchEvent(new Event('input'));
                });
            }

            form.addEventListener('submit', function (e) {
                var valid = true;
                if (!nameEl  || !nameEl.value.trim())  { showFieldErr(nameEl,  'err-name-identity');  valid = false; }
                if (!classEl || !classEl.value.trim()) { showFieldErr(classEl, 'err-class-identity'); valid = false; }
                if (!nisInput || nisInput.value.trim() === '' || /[^0-9]/.test(nisInput.value)) {
                    showFieldErr(nisInput, 'nis-inline-error'); valid = false;
                }
                if (!valid) e.preventDefault();
            });
        })();
    </script>
@endif

<script>
// ============================================================
// SIDEBAR TOGGLE (Collapsible)
// ============================================================
(function () {
    const sidebar  = document.getElementById('main-sidebar');
    const toggle   = document.getElementById('sidebar-toggle');
    const STORAGE_KEY = 'wms_sidebar_collapsed';

    // Restore saved state
    const isCollapsed = localStorage.getItem(STORAGE_KEY) === '1';
    if (isCollapsed) applySidebarState(true);

    toggle?.addEventListener('click', function () {
        const collapsed = sidebar.classList.contains('wms-sidebar-collapsed');
        applySidebarState(!collapsed);
        localStorage.setItem(STORAGE_KEY, !collapsed ? '1' : '0');
    });

    function applySidebarState(collapse) {
        if (collapse) {
            sidebar.classList.add('wms-sidebar-collapsed');
            sidebar.style.width = '0px';
            sidebar.style.overflow = 'hidden';
            sidebar.style.borderRight = 'none';
        } else {
            sidebar.classList.remove('wms-sidebar-collapsed');
            sidebar.style.width = '240px';
            sidebar.style.overflow = '';
            sidebar.style.borderRight = '';
        }
    }
})();
</script>
</body>
</html>