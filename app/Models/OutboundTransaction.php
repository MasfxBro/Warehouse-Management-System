<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutboundTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'outbound_transactions';
    protected $primaryKey = 'Outbound_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'No_Shipping', 'Tanggal', 'Customer_ID', 'User_ID',
        'picking_status', 'priority', 'Nama_Penerima', 'Catatan',
    ];

    protected $casts = [
        'Tanggal' => 'date',
        'User_ID' => 'integer',
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

    public function isComplete(): bool { return $this->picking_status === 'complete'; }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'high'   => 'High',
            'normal' => 'Normal',
            default  => 'Decent',
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'Customer_ID', 'Customer_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }

    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'Outbound_ID', 'Outbound_ID');
    }
}
