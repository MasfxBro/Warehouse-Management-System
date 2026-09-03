<?php

namespace App\Models;

use App\Traits\HasTitleCaseAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Supplier
 *
 * Data supplier/pemasok barang (PURE READ-ONLY di Master Data, diisi dari Inbound).
 */
class Supplier extends Model
{
    use HasFactory, HasTitleCaseAttributes;

    protected $table      = 'suppliers';
    protected $primaryKey = 'Supplier_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['Nama', 'Kontak', 'No_Kontak', 'Email', 'Alamat'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::orderedUuid();
            }
        });
    }

    // =========================================================
    // AUTO-TITLE CASE MUTATORS (BACKEND ENGINE)
    // =========================================================

    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => self::formatTitleCase($value),
        );
    }

    protected function noKontak(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? trim($value) : null,
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    protected function alamat(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => self::formatTitleCase($value),
        );
    }

    // =========================================================
    // RELASI
    // =========================================================

    public function inboundTransactions(): HasMany
    {
        return $this->hasMany(InboundTransaction::class, 'Supplier_ID', 'Supplier_ID');
    }
}
