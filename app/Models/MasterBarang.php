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
        'stok_real',
        'harga',
        'satuan',
        'Barcode_ID',
        'Rack_ID',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Min_Stok'  => 'integer',
        'stok_real' => 'integer',
        'harga'     => 'decimal:2',
        'Rack_ID'   => 'integer',
    ];

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

    /**
     * Satu barang bisa memiliki banyak record stock opname.
     */
    public function stockOpname(): HasMany
    {
        return $this->hasMany(StockOpname::class, 'SKU', 'SKU');
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * Cek apakah stok berada di bawah minimum (perlu reorder).
     */
    public function needsReorder(): bool
    {
        return $this->stok_real < $this->Min_Stok;
    }

    /**
     * Get status stok (AMAN/REORDER).
     */
    public function getStockStatus(): string
    {
        return $this->needsReorder() ? 'REORDER' : 'AMAN';
    }

    /**
     * Get nilai persediaan (stok * harga).
     */
    public function getNilaiPersediaan(): float
    {
        return $this->stok_real * $this->harga;
    }
}
