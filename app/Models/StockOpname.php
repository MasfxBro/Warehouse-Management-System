<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpname extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $table = 'stock_opnames';

    protected $primaryKey = 'Opname_ID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['SKU', 'User_ID', 'Tanggal', 'Kondisi', 'Practice_Session_ID'];

    protected $casts = [
        'Tanggal' => 'date',
        'User_ID' => 'integer',
    ];

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'SKU', 'SKU');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'id');
    }
}
