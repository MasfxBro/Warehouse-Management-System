<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['user_id', 'operator_name', 'action', 'Practice_Session_ID'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function record(string $action): self
    {
        $user = Auth::user();
        $userId = $user?->id;
        $operatorName = 'Sistem / Tamu';

        if ($user) {
            if ($user->isAdmin()) {
                $operatorName = 'Guru: '.$user->name;
            } else {
                $identity = session('student_identity');
                if ($identity && ! empty($identity['name'])) {
                    $operatorName = "Operator: {$identity['name']} | {$identity['class']} (NIS: {$identity['nis']})";
                } else {
                    $operatorName = 'Siswa: '.$user->name;
                }
            }
        }

        return self::create([
            'user_id' => $userId,
            'operator_name' => $operatorName,
            'action' => $action,
            'Practice_Session_ID' => PracticeSession::current()?->Practice_Session_ID,
        ]);
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'Practice_Session_ID');
    }
}
