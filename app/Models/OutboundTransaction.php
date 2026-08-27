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
 * Header transaksi pengiriman barang ke customer (SJ-YYYYMMDD-XXXX).
 *
 * @property int         $Outbound_ID
 * @property string      $No_Shipping
 * @property \Carbon\Carbon $Tanggal
 * @property int         $Customer_ID
 * @property int         $User_ID
 * @property string      $picking_status  — 'not_complete' | 'complete'
 * @property string      $priority        — 'high' | 'normal' | 'decent'
 * @property string|null $Nama_Penerima
 * @property string|null $Catatan
 */
class OutboundTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'outbound_transactions';
    protected $primaryKey = 'Outbound_ID';

    protected $fillable = [
        'No_Shipping',
        'Tanggal',
        'Customer_ID',
        'User_ID',
        'picking_status',
        'priority',
        'Nama_Penerima',
        'Catatan',
    ];

    protected $casts = [
        'Tanggal'     => 'date',
        'Customer_ID' => 'integer',
        'User_ID'     => 'integer',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'Customer_ID', 'Customer_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }

    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'Outbound_ID', 'Outbound_ID');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public function isComplete(): bool
    {
        return $this->picking_status === 'complete';
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'high'   => 'bg-red-100 text-red-800 border border-red-300',
            'normal' => 'bg-amber-100 text-amber-800 border border-amber-300',
            default  => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'high'   => 'High',
            'normal' => 'Normal',
            default  => 'Decent',
        };
    }
}
