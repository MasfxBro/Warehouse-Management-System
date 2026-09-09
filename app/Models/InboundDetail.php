<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboundDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'inbound_details';
    protected $primaryKey = 'Detail_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['Inbound_ID', 'SKU', 'Rack_ID', 'Qty', 'No_Resi_Supplier', 'Batch'];

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

    public function inboundTransaction(): BelongsTo
    {
        return $this->belongsTo(InboundTransaction::class, 'Inbound_ID', 'Inbound_ID');
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
