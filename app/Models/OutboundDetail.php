<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutboundDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'outbound_details';
    protected $primaryKey = 'Detail_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['Outbound_ID', 'SKU', 'Rack_ID', 'Qty'];

    protected $casts = ['Qty' => 'integer'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::orderedUuid();
            }
        });
    }

    public function outboundTransaction(): BelongsTo
    {
        return $this->belongsTo(OutboundTransaction::class, 'Outbound_ID', 'Outbound_ID');
    }

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'SKU', 'SKU');
    }

    public function rackLocation(): BelongsTo
    {
        return $this->belongsTo(RackLocation::class, 'Rack_ID', 'Rack_ID');
    }
}
