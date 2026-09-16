<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboundTransaction extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    protected $table = 'inbound_transactions';

    protected $primaryKey = 'Inbound_ID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'No_Receiving', 'Tanggal', 'Supplier_ID', 'User_ID', 'Catatan',
        'transaction_status', 'Cancelled_At', 'Cancelled_By', 'Cancellation_Reason', 'Practice_Session_ID',
    ];

    protected $casts = [
        'Tanggal' => 'date',
        'User_ID' => 'integer',
        'Cancelled_At' => 'datetime',
    ];

    public function getNoResiAttribute(): string
    {
        return $this->No_Receiving;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'Supplier_ID', 'Supplier_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }

    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'Inbound_ID', 'Inbound_ID');
    }

    public function allInboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'Inbound_ID', 'Inbound_ID')->withTrashed();
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'Practice_Session_ID');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Cancelled_By');
    }

    public function isCancelled(): bool
    {
        return $this->transaction_status === 'cancelled';
    }

    public function getTotalNilaiAttribute(): int
    {
        $details = $this->isCancelled() ? $this->allInboundDetails : $this->inboundDetails;

        return (int) $details->sum('subtotal');
    }
}
