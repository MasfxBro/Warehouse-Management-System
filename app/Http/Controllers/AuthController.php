<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses otentikasi login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($credentials['login']);
        $password   = $credentials['password'];

        // Cek login via Email atau Username ("admin" / "siswa")
        $email = $loginInput;
        if (!filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            if (strtolower($loginInput) === 'admin') {
                $email = 'admin@wms.local';
            } elseif (strtolower($loginInput) === 'siswa') {
                $email = 'siswa@wms.local';
            }
        }

        if (Auth::attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            ActivityLog::record("Pengguna [{$user->name}] ({$user->role->label()}) berhasil login ke sistem.");

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'login' => 'Kredensial login tidak cocok dengan data kami.',
        ])->onlyInput('login');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::record("Pengguna [" . Auth::user()->name . "] logout dari sistem.");
        }

        $request->session()->forget('student_identity');
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
