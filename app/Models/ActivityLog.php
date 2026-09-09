<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['user_id', 'operator_name', 'action'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::orderedUuid();
            }
        });
    }

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
