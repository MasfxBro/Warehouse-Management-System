<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: InboundTransaction
 *
 * Merepresentasikan header transaksi penerimaan barang dari supplier.
 * Menggunakan SoftDeletes agar riwayat transaksi tidak terhapus permanen.
 *
 * @property int         $Inbound_ID
 * @property string      $No_Receiving
 * @property \Carbon\Carbon $Tanggal
 * @property int         $Supplier_ID
 * @property int         $User_ID
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class InboundTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /** 
     * Nama tabel sesuai ERD.
     */
    protected $table = 'inbound_transactions';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Inbound_ID';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'No_Receiving',
        'Tanggal',
        'Supplier_ID',
        'User_ID',
        'status',
        'notes',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Tanggal'     => 'date',
        'Supplier_ID' => 'integer',
        'User_ID'     => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Transaksi inbound milik satu supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'Supplier_ID', 'Supplier_ID');
    }

    /**
     * Transaksi inbound diproses oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }

    /**
     * Satu transaksi inbound memiliki banyak detail baris barang.
     */
    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'Inbound_ID', 'Inbound_ID');
    }
}
