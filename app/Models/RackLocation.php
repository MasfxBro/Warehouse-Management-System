<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: RackLocation
 *
 * Merepresentasikan lokasi rak penyimpanan di gudang.
 * Direferensikan oleh master_barang, inbound_details, dan outbound_details.
 *
 * @property int         $Rack_ID
 * @property string      $Kode_Rak
 * @property string      $Aisle
 * @property string      $Level
 * @property int         $Kapasitas
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RackLocation extends Model
{
    use HasFactory;

    /**
     * Nama tabel sesuai ERD — tidak menggunakan konvensi snake_case Laravel.
     */
    protected $table = 'rack_locations';

    /**
     * Primary key sesuai ERD.
     */
    protected $primaryKey = 'Rack_ID';

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'Kode_Rak',
        'Aisle',
        'Level',
        'Kapasitas',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'Kapasitas' => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Satu rack_location memiliki banyak master_barang (sebagai lokasi default).
     */
    public function masterBarang(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'Rack_ID', 'Rack_ID');
    }

    /**
     * Satu rack_location memiliki banyak inbound_details.
     */
    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'Rack_ID', 'Rack_ID');
    }

    /**
     * Satu rack_location memiliki banyak outbound_details.
     */
    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'Rack_ID', 'Rack_ID');
    }
}
