<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboundTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'inbound_transactions';
    protected $primaryKey = 'Inbound_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['No_Receiving', 'Tanggal', 'Supplier_ID', 'User_ID', 'Catatan'];

    protected $casts = [
        'Tanggal'    => 'date',
        'User_ID'    => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::orderedUuid();
            }
        });
    }

    public function getNoResiAttribute(): string { return $this->No_Receiving; }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'Supplier_ID', 'Supplier_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }

    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'Inbound_ID', 'Inbound_ID');
    }
}
