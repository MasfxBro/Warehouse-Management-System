<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Supplier
 *
 * Merepresentasikan data master supplier/pemasok barang.
 *
 * @property int         $Supplier_ID
 * @property string      $Nama
 * @property string|null $Kontak
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Supplier extends Model
{
    use HasFactory;

    /**
     * Nama tabel sesuai ERD.
     */
    protected $table = 'suppliers';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Supplier_ID';

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
     * Satu supplier memiliki banyak inbound_transactions.
     */
    public function inboundTransactions(): HasMany
    {
        return $this->hasMany(InboundTransaction::class, 'Supplier_ID', 'Supplier_ID');
    }
}
