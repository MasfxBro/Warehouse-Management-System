<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeSession extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $primaryKey = 'Practice_Session_ID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['Nama', 'Kelas', 'Tanggal', 'Status', 'Created_By', 'Opened_At', 'Closed_At'];

    protected $casts = [
        'Tanggal' => 'date',
        'Opened_At' => 'datetime',
        'Closed_At' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Created_By');
    }

    public function scopeActive($query)
    {
        return $query->where('Status', 'active');
    }

    public static function current(): ?self
    {
        return self::active()->latest('Opened_At')->first();
    }
}
