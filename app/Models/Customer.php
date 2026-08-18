<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Customer
 *
 * Merepresentasikan data master pelanggan penerima pengiriman barang.
 *
 * @property int         $Customer_ID
 * @property string      $Nama
 * @property string|null $Alamat
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * Nama tabel sesuai ERD.
     */
    protected $table = 'customers';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Customer_ID';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'Nama',
        'Alamat',
        'Kontak',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Satu customer memiliki banyak outbound_transactions.
     */
    public function outboundTransactions(): HasMany
    {
        return $this->hasMany(OutboundTransaction::class, 'Customer_ID', 'Customer_ID');
    }
}
