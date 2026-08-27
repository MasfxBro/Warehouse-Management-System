<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Model: ActivityLog
 *
 * Mencatat seluruh riwayat aktivitas yang dilakukan oleh Guru (Admin) maupun Siswa (Operator).
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $operator_name
 * @property string $action
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'operator_name',
        'action',
    ];

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper static untuk mencatat aktivitas secara otomatis.
     */
    public static function record(string $action): self
    {
        $user = Auth::user();
        $userId = $user?->id;
        $operatorName = 'Sistem / Tamu';

        if ($user) {
            if ($user->isAdmin()) {
                $operatorName = 'Guru: ' . $user->name;
            } else {
                $identity = session('student_identity');
                if ($identity && !empty($identity['name'])) {
                    $operatorName = "Operator: {$identity['name']} | {$identity['class']} (NIS: {$identity['nis']})";
                } else {
                    $operatorName = 'Siswa: ' . $user->name;
                }
            }
        }

        return self::create([
            'user_id'       => $userId,
            'operator_name' => $operatorName,
            'action'        => $action,
        ]);
    }
}
