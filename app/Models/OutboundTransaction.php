<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutboundTransaction extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    protected $table = 'outbound_transactions';

    protected $primaryKey = 'Outbound_ID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'No_Shipping', 'Tanggal', 'Customer_ID', 'User_ID',
        'picking_status', 'priority', 'Nama_Penerima', 'Catatan',
        'transaction_status', 'Cancelled_At', 'Cancelled_By', 'Cancellation_Reason', 'Practice_Session_ID',
    ];

    protected $casts = [
        'Tanggal' => 'date',
        'User_ID' => 'integer',
        'Cancelled_At' => 'datetime',
    ];

    public function isComplete(): bool
    {
        return ! $this->isCancelled() && $this->picking_status === 'complete';
    }

    public function isCancelled(): bool
    {
        return $this->transaction_status === 'cancelled';
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'high' => 'High',
            'normal' => 'Normal',
            default => 'Decent',
        };
    }

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

    public function allOutboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'Outbound_ID', 'Outbound_ID')->withTrashed();
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'Practice_Session_ID');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Cancelled_By');
    }
}
