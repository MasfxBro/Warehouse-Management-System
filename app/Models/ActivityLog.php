<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 
        'operator_name', 
        'action',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function record(string $action): self
    {
        $user         = Auth::user();
        $userId       = $user?->id;
        $operatorName = 'Sistem / Tamu';

        if ($user) {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
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

        return static::create([
            'user_id'       => $userId,
            'operator_name' => $operatorName,
            'action'        => $action,
        ]);
    }
}