<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiToken;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['login' => ['required', 'string'], 'password' => ['required', 'string']]);
        $login = strtolower(trim($data['login']));
        $email = match ($login) {
            'admin' => 'admin@wms.local',
            'siswa' => 'siswa@wms.local',
            default => $login,
        };
        $user = User::where('email', $email)->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Kredensial login tidak cocok.'], 422);
        }

        $plainToken = Str::random(64);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'flutter',
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
        ]);
        auth()->setUser($user);
        ActivityLog::record("Pengguna [{$user->name}] ({$user->role->label()}) login melalui aplikasi Flutter.");

        return response()->json([
            'success' => true,
            'token' => $plainToken,
            'user' => $this->userPayload($user),
            'requires_student_identity' => $user->isUser() && ! $token->hasStudentIdentity(),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $token = $request->attributes->get('api_token');
        $session = PracticeSession::current();
        return response()->json(['success' => true, 'data' => [
            'user' => $this->userPayload($request->user()),
            'student_identity' => $token->hasStudentIdentity() ? [
                'name' => $token->student_name, 'class' => $token->student_class, 'nis' => $token->student_nis,
            ] : null,
            'requires_student_identity' => $request->user()->isUser() && ! $token->hasStudentIdentity(),
            'practice_session' => $session ? [
                'id' => $session->Practice_Session_ID, 'name' => $session->Nama, 'class' => $session->Kelas,
                'date' => $session->Tanggal?->format('Y-m-d'), 'status' => $session->Status,
            ] : null,
        ]]);
    }

    public function saveIdentity(Request $request): JsonResponse
    {
        abort_unless($request->user()->isUser(), 403, 'Identitas siswa hanya untuk akun operator.');
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'class' => ['required', 'string', 'max:100'],
            'nis' => ['required', 'regex:/^\\d+$/'],
        ]);
        $request->attributes->get('api_token')->update([
            'student_name' => trim($data['name']), 'student_class' => trim($data['class']), 'student_nis' => trim($data['nis']),
        ]);
        ActivityLog::record("Identitas siswa [{$data['name']}] kelas [{$data['class']}] NIS [{$data['nis']}] diaktifkan dari aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Identitas siswa berhasil disimpan.']);
    }

    public function resetIdentity(Request $request): JsonResponse
    {
        $request->attributes->get('api_token')->update(['student_name' => null, 'student_class' => null, 'student_nis' => null]);
        return response()->json(['success' => true, 'message' => 'Identitas siswa telah direset.']);
    }

    public function logout(Request $request): JsonResponse
    {
        ActivityLog::record('Pengguna ['.$request->user()->name.'] logout dari aplikasi Flutter.');
        $request->attributes->get('api_token')->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil.']);
    }

    private function userPayload(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role->value, 'role_label' => $user->role->label()];
    }
}
