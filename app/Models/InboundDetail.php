<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboundDetail extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    protected $table = 'inbound_details';

    protected $primaryKey = 'Detail_ID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['Inbound_ID', 'SKU', 'Rack_ID', 'Qty', 'Harga_Satuan', 'No_Resi_Supplier', 'Batch'];

    protected $casts = [
        'Qty' => 'integer',
        'Harga_Satuan' => 'integer',
    ];

    public function getSubtotalAttribute(): int
    {
        return $this->Qty * $this->Harga_Satuan;
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
