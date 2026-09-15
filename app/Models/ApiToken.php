<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id', 'name', 'token_hash', 'student_name', 'student_class',
        'student_nis', 'last_used_at', 'expires_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasStudentIdentity(): bool
    {
        return filled($this->student_name) && filled($this->student_class) && filled($this->student_nis);
    }
}
