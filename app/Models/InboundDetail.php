<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: InboundDetail
 *
 * Detail baris barang dalam transaksi penerimaan inbound.
 */
class InboundDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inbound_details';
    protected $primaryKey = 'Detail_ID';

    protected $fillable = [
        'Inbound_ID',
        'SKU',
        'Rack_ID',
        'Qty',
        'No_Resi_Supplier',
        'Batch',
    ];

    protected $casts = [
        'Inbound_ID' => 'integer',
        'Rack_ID'    => 'integer',
        'Qty'        => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

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
