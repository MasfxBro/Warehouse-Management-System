<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: OutboundTransaction
 *
 * Merepresentasikan header transaksi pengiriman barang ke customer.
 * Menggunakan SoftDeletes agar riwayat transaksi tidak terhapus permanen.
 *
 * @property int         $Outbound_ID
 * @property string      $No_Shipping
 * @property \Carbon\Carbon $Tanggal
 * @property int         $Customer_ID
 * @property string|null $No_Surat_Jalan
 * @property int         $User_ID
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class OutboundTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel sesuai ERD.
     */
    protected $table = 'outbound_transactions';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Outbound_ID';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'No_Shipping',
        'Tanggal',
        'Customer_ID',
        'No_Surat_Jalan',
        'User_ID',
        'status',
        'notes',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Tanggal'     => 'date',
        'Customer_ID' => 'integer',
        'User_ID'     => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Transaksi outbound ditujukan ke satu customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'Customer_ID', 'Customer_ID');
    }

    /**
     * Transaksi outbound diproses oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }

    /**
     * Satu transaksi outbound memiliki banyak detail baris barang.
     */
    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'Outbound_ID', 'Outbound_ID');
    }
}
