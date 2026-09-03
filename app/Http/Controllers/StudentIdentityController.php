<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class StudentIdentityController extends Controller
{
    /**
     * Simpan data identitas siswa ke dalam session active.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'class' => 'required|string|max:100',
            'nis'   => 'required|digits_between:1,20',
        ], [
            'name.required'  => 'Nama Lengkap wajib diisi.',
            'class.required' => 'Kelas wajib diisi.',
            'nis.required'   => 'NIS wajib diisi.',
            'nis.digits_between' => 'NIS harus berupa angka.',
        ]);

        $identity = [
            'name'  => trim($validated['name']),
            'class' => trim($validated['class']),
            'nis'   => trim($validated['nis']),
        ];

        session(['student_identity' => $identity]);
        session()->save(); // Paksa tulis session sebelum redirect

        ActivityLog::record("Siswa Login & Isi Identitas: {$identity['name']} ({$identity['class']} / NIS: {$identity['nis']})");

        return redirect()->route('dashboard')->with('success', 'Identitas praktikum berhasil disimpan. Selamat belajar!');
    }

    /**
     * Reset identitas siswa (untuk pengoperan sesi ke siswa lain).
     */
    public function reset(Request $request)
    {
        if (session()->has('student_identity')) {
            $oldIdentity = session('student_identity');
            ActivityLog::record("Siswa mengosongkan/mengganti identitas sesi praktikum ({$oldIdentity['name']}).");
            session()->forget('student_identity');
        }

        return redirect()->route('dashboard')->with('info', 'Identitas siswa di-reset. Silakan isi identitas baru.');
    }
}
