<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: MasterBarang
 *
 * Merepresentasikan data master barang/produk yang dikelola di gudang.
 * Primary Key adalah SKU (string) — bukan auto-increment.
 *
 * @property string      $SKU
 * @property string      $Nama
 * @property string      $Kategori
 * @property int         $Min_Stok
 * @property string|null $Barcode_ID
 * @property int|null    $Rack_ID
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MasterBarang extends Model
{
    use HasFactory;

    /**
     * Nama tabel sesuai ERD.
     */
    protected $table = 'master_barang';

    /**
     * Primary key sesuai ERD — string, bukan auto-increment integer.
     */
    protected $primaryKey = 'SKU';

    /**
     * Primary key bukan auto-increment karena bertipe string.
     */
    public $incrementing = false;

    /**
     * Tipe primary key adalah string.
     */
    protected $keyType = 'string';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'SKU',
        'Nama',
        'Kategori',
        'Min_Stok',
        'Barcode_ID',
        'Rack_ID',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Min_Stok' => 'integer',
        'Rack_ID'  => 'integer',
    ];

    // =========================================================
    // DYNAMIC ACCESSORS
    // =========================================================

    /**
     * Accessor untuk stok real-time (Inbound Qty - Outbound Qty).
     *
     * Menggunakan property (tanpa `()`) supaya pakai eager-loaded collection
     * jika relasi sudah di-load via with(), bukan query baru per item.
     */
    public function getStokAttribute(): int
    {
        // Jika relasi sudah eager-loaded, gunakan collection langsung (O(1))
        // Jika belum, baru query DB — fallback aman tapi hindari di loop
        if ($this->relationLoaded('inboundDetails') && $this->relationLoaded('outboundDetails')) {
            $inbound  = $this->inboundDetails->sum('Qty');
            $outbound = $this->outboundDetails->sum('Qty');
        } else {
            $inbound  = $this->inboundDetails()->sum('Qty');
            $outbound = $this->outboundDetails()->sum('Qty');
        }
        return max(0, $inbound - $outbound);
    }

    /**
     * Accessor untuk harga barang per unit (default: Rp 50.000).
     */
    public function getHargaAttribute(): int
    {
        return 50000;
    }

    /**
     * Accessor untuk nilai total barang (Stok * Harga).
     */
    public function getNilaiBarangAttribute(): int
    {
        return $this->stok * $this->harga;
    }

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Setiap barang memiliki lokasi default di satu rack_location.
     */
    public function rackLocation(): BelongsTo
    {
        return $this->belongsTo(RackLocation::class, 'Rack_ID', 'Rack_ID');
    }

    /**
     * Satu barang bisa muncul di banyak inbound_details.
     */
    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'SKU', 'SKU');
    }

    /**
     * Satu barang bisa muncul di banyak outbound_details.
     */
    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'SKU', 'SKU');
    }
}
