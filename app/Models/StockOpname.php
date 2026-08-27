<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: StockOpname
 *
 * Pencatatan kondisi fisik barang hasil inspeksi lapangan.
 * BUKAN mengubah stok — hanya deskripsi kondisi fisik per tanggal.
 *
 * @property int    $Opname_ID
 * @property string $SKU
 * @property int    $User_ID
 * @property string $Tanggal
 * @property string $Kondisi
 */
class StockOpname extends Model
{
    use HasFactory;

    protected $table      = 'stock_opnames';
    protected $primaryKey = 'Opname_ID';

    protected $fillable = [
        'SKU',
        'User_ID',
        'Tanggal',
        'Kondisi',
    ];

    protected $casts = [
        'Tanggal' => 'date',
        'User_ID' => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'SKU', 'SKU');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }
}
