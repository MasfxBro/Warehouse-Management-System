<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: OutboundDetail
 *
 * Merepresentasikan detail per-baris barang dalam satu transaksi outbound.
 * Setiap record mencatat SKU, rak sumber, dan jumlah yang dikirim.
 * Menggunakan SoftDeletes agar konsisten dengan tabel header.
 *
 * @property int         $Detail_ID
 * @property int         $Outbound_ID
 * @property string      $SKU
 * @property int         $Rack_ID
 * @property int         $Qty
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class OutboundDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel sesuai ERD.
     */
    protected $table = 'outbound_details';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Detail_ID';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'Outbound_ID',
        'SKU',
        'Rack_ID',
        'Qty',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Outbound_ID' => 'integer',
        'Rack_ID'     => 'integer',
        'Qty'         => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Detail outbound milik satu transaksi outbound (header).
     */
    public function outboundTransaction(): BelongsTo
    {
        return $this->belongsTo(OutboundTransaction::class, 'Outbound_ID', 'Outbound_ID');
    }

    /**
     * Detail outbound mereferensikan satu master barang.
     */
    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'SKU', 'SKU');
    }

    /**
     * Detail outbound mereferensikan satu lokasi rak.
     */
    public function rackLocation(): BelongsTo
    {
        return $this->belongsTo(RackLocation::class, 'Rack_ID', 'Rack_ID');
    }
}
