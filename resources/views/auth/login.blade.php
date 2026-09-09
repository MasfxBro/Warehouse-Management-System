<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WMS Prototipe 2</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#f7f9fb] flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center shadow-sm">
            <i class="fa-solid fa-warehouse text-white text-base"></i>
        </div>
        <div>
            <p class="text-[15px] font-bold text-slate-900 leading-tight">WMS Prototipe 2</p>
            <p class="text-[11px] text-slate-400">Warehouse Management System</p>
        </div>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-sm overflow-hidden">
        <div class="px-6 pt-6 pb-2">
            <h1 class="text-lg font-bold text-slate-900">Masuk ke Sistem</h1>
            <p class="text-xs text-slate-400 mt-1">Gunakan kredensial yang diberikan oleh instruktur.</p>
        </div>

        <form action="{{ route('login.store') }}" method="POST" class="px-6 pb-6 pt-4 space-y-4" id="login-form" novalidate>
            @csrf

            @if(session('error'))
                <div class="alert-error text-xs">
                    <i class="fa-solid fa-triangle-exclamation flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="alert-info text-xs">
                    <i class="fa-solid fa-circle-info flex-shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error text-xs">
                    <i class="fa-solid fa-triangle-exclamation flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div>
                <label class="wms-label">Username atau Email</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fa-solid fa-user text-xs"></i>
                    </span>
                    <input type="text" name="login"
                           id="input-login"
                           value="{{ old('login') }}"
                           required autofocus
                           placeholder="admin / siswa"
                           class="wms-input" style="padding-left: 2.25rem;">
                </div>
                <p id="err-input-login" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Username atau email wajib diisi.
                </p>
            </div>

            <div>
                <label class="wms-label">Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </span>
                    <input type="password" name="password"
                           id="input-password"
                           required
                           placeholder="••••••••"
                           class="wms-input" style="padding-left: 2.25rem;">
                </div>
                <p id="err-input-password" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Password wajib diisi.
                </p>
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-[#0058be] hover:bg-[#004499] text-white font-bold text-sm rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                <i class="fa-solid fa-right-to-bracket text-xs"></i>
                Masuk
            </button>
        </form>
    </div>

    <!-- Kredensial hint -->
    <div class="mt-5 bg-[#f2f4f6] border border-[#e2e8f0] rounded-xl px-5 py-4 text-[11px] text-slate-500 space-y-1.5">
        <p class="font-bold text-slate-700 flex items-center gap-1.5">
            <i class="fa-solid fa-circle-info text-[#0058be]"></i>
            Kredensial Default
        </p>
        <div class="flex items-center gap-2">
            <span class="w-20 font-medium text-slate-600">Guru (Admin)</span>
            <code class="font-mono bg-white border border-[#e2e8f0] px-2 py-0.5 rounded text-slate-800">admin</code>
            <span class="text-slate-400">/</span>
            <code class="font-mono bg-white border border-[#e2e8f0] px-2 py-0.5 rounded text-slate-800">password</code>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-20 font-medium text-slate-600">Siswa (User)</span>
            <code class="font-mono bg-white border border-[#e2e8f0] px-2 py-0.5 rounded text-slate-800">siswa</code>
            <span class="text-slate-400">/</span>
            <code class="font-mono bg-white border border-[#e2e8f0] px-2 py-0.5 rounded text-slate-800">password</code>
        </div>
    </div>

    <p class="text-center text-[10px] text-slate-400 mt-5">WMS Prototipe 2 &copy; {{ date('Y') }} — SMK Logistik</p>
</div>

</body>
</html>

<script>
(function () {
    var form = document.getElementById('login-form');
    if (!form) return;
    function showErr(id, msg) {
        var p = document.getElementById('err-' + id);
        var inp = document.getElementById(id);
        if (p) { p.textContent = ''; p.innerHTML = '<i class="fa-solid fa-circle-exclamation text-[10px] mr-1"></i>' + msg; p.classList.remove('hidden'); p.classList.add('flex'); }
        if (inp) inp.classList.add('border-red-400');
    }
    function clearErr(id) {
        var p = document.getElementById('err-' + id);
        var inp = document.getElementById(id);
        if (p) { p.classList.add('hidden'); p.classList.remove('flex'); }
        if (inp) inp.classList.remove('border-red-400');
    }
    ['input-login', 'input-password'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () { clearErr(id); });
    });
    form.addEventListener('submit', function (e) {
        var valid = true;
        var login = document.getElementById('input-login');
        var pass  = document.getElementById('input-password');
        if (!login || !login.value.trim()) { showErr('input-login', 'Username atau email wajib diisi.'); valid = false; }
        if (!pass  || !pass.value.trim())  { showErr('input-password', 'Password wajib diisi.'); valid = false; }
        if (!valid) { e.preventDefault(); }
    });
})();
</script>
</body>
</html>
