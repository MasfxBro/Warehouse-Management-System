<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RackLocation extends Model
{
    use HasFactory;

    protected $table      = 'rack_locations';
    protected $primaryKey = 'Rack_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['Kode_Rak', 'Aisle', 'Level', 'Kapasitas'];
    protected $casts    = ['Kapasitas' => 'integer'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::orderedUuid();
            }
        });
    }

    public function getKapasitasTerpakaiAttribute(): int
    {
        $inbound  = $this->inboundDetails()->sum('Qty');
        $outbound = $this->outboundDetails()->sum('Qty');
        return max(0, $inbound - $outbound);
    }

    public function getStatusKapasitasAttribute(): string
    {
        $max  = $this->Kapasitas;
        $used = $this->kapasitas_terpakai;
        if ($max <= 0) return 'Tersedia';
        $ratio = $used / $max;
        if ($ratio >= 1.0) return 'Penuh';
        if ($ratio >= 0.8) return 'Hampir Penuh';
        return 'Tersedia';
    }

    public function masterBarang(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'Rack_ID', 'Rack_ID');
    }

    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'Rack_ID', 'Rack_ID');
    }

    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'Rack_ID', 'Rack_ID');
    }
}
