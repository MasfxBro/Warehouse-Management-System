  <?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: InboundDetail
 *
 * Merepresentasikan detail per-baris barang dalam satu transaksi inbound.
 * Setiap record mencatat SKU, rak tujuan, jumlah, dan nomor batch.
 * Menggunakan SoftDeletes agar konsisten dengan tabel header.
 *
 * @property int         $Detail_ID
 * @property int         $Inbound_ID
 * @property string      $SKU
 * @property int         $Rack_ID
 * @property int         $Qty
 * @property string|null $Batch
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class InboundDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel sesuai ERD.
     */
    protected $table = 'inbound_details';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Detail_ID';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'Inbound_ID',
        'SKU',
        'Rack_ID',
        'Qty',
        'Batch',
        'expired_date',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Inbound_ID'   => 'integer',
        'Rack_ID'      => 'integer',
        'Qty'          => 'integer',
        'expired_date' => 'date',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Detail inbound milik satu transaksi inbound (header).
     */
    public function inboundTransaction(): BelongsTo
    {
        return $this->belongsTo(InboundTransaction::class, 'Inbound_ID', 'Inbound_ID');
    }

    /**
     * Detail inbound mereferensikan satu master barang.
     */
    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'SKU', 'SKU');
    }

    /**
     * Detail inbound mereferensikan satu lokasi rak.
     */
    public function rackLocation(): BelongsTo
    {
        return $this->belongsTo(RackLocation::class, 'Rack_ID', 'Rack_ID');
    }
}
