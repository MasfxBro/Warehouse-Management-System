<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: StockOpname
 *
 * Merepresentasikan hasil audit fisik stok (stock opname).
 * Mencatat selisih antara stok sistem dengan stok fisik di lapangan.
 *
 * @property int         $opname_id
 * @property string      $SKU
 * @property \Carbon\Carbon $tanggal_opname
 * @property int         $stok_sistem
 * @property int         $stok_fisik
 * @property int         $variance
 * @property string      $status
 * @property string|null $action_taken
 * @property string|null $notes
 * @property int         $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StockOpname extends Model
{
    use HasFactory;

    /**
     * Nama tabel.
     */
    protected $table = 'stock_opname';

    /**
     * Primary key.
     */
    protected $primaryKey = 'opname_id';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'SKU',
        'tanggal_opname',
        'stok_sistem',
        'stok_fisik',
        'variance',
        'status',
        'action_taken',
        'notes',
        'user_id',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'tanggal_opname' => 'date',
        'stok_sistem'    => 'integer',
        'stok_fisik'     => 'integer',
        'variance'       => 'integer',
        'user_id'        => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Stock opname mereferensikan satu master barang.
     */
    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'SKU', 'SKU');
    }

    /**
     * Stock opname dilakukan oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * Hitung variance otomatis sebelum disimpan.
     */
    protected static function booted(): void
    {
        static::creating(function (StockOpname $opname) {
            // Auto-calculate variance
            $opname->variance = $opname->stok_fisik - $opname->stok_sistem;
            
            // Auto-set status
            $opname->status = ($opname->variance == 0) ? 'MATCH' : 'SELISIH';
        });

        static::updating(function (StockOpname $opname) {
            // Recalculate jika stok berubah
            if ($opname->isDirty(['stok_fisik', 'stok_sistem'])) {
                $opname->variance = $opname->stok_fisik - $opname->stok_sistem;
                $opname->status = ($opname->variance == 0) ? 'MATCH' : 'SELISIH';
            }
        });
    }

    /**
     * Cek apakah ada selisih.
     */
    public function hasVariance(): bool
    {
        return $this->variance != 0;
    }

    /**
     * Get variance dengan tanda (+/-).
     */
    public function getVarianceWithSign(): string
    {
        return ($this->variance >= 0 ? '+' : '') . $this->variance;
    }
}
