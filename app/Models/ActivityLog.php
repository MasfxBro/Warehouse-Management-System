<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Concerns\HasUuids;
=======
use App\Traits\HasUuidPrimaryKey;
>>>>>>> 2ecb809d71c906c3fed40296eed7f3d2352a2c0c
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
<<<<<<< HEAD
    use HasFactory, HasUuids;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 
        'operator_name', 
        'action',
    ];
=======
    use HasFactory, HasUuidPrimaryKey;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['user_id', 'operator_name', 'action', 'Practice_Session_ID'];
>>>>>>> 2ecb809d71c906c3fed40296eed7f3d2352a2c0c

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
<<<<<<< HEAD
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $operatorName = 'Guru: ' . $user->name;
=======
            if ($user->isAdmin()) {
                $operatorName = 'Guru: '.$user->name;
>>>>>>> 2ecb809d71c906c3fed40296eed7f3d2352a2c0c
            } else {
                $identity = session('student_identity');
                if ($identity && ! empty($identity['name'])) {
                    $operatorName = "Operator: {$identity['name']} | {$identity['class']} (NIS: {$identity['nis']})";
                } else {
                    $operatorName = 'Siswa: '.$user->name;
                }
            }
        }

<<<<<<< HEAD
        return static::create([
            'user_id'       => $userId,
=======
        return self::create([
            'user_id' => $userId,
>>>>>>> 2ecb809d71c906c3fed40296eed7f3d2352a2c0c
            'operator_name' => $operatorName,
            'action' => $action,
            'Practice_Session_ID' => PracticeSession::current()?->Practice_Session_ID,
        ]);
    }
<<<<<<< HEAD
}
=======

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'Practice_Session_ID');
    }
}
>>>>>>> 2ecb809d71c906c3fed40296eed7f3d2352a2c0c
