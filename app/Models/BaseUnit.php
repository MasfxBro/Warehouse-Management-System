<?php

namespace App\Models;

use App\Support\UnitNormalizer;
use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseUnit extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $table = 'base_units';

    protected $primaryKey = 'Unit_ID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['Nama'];

    public function setNamaAttribute(?string $value): void
    {
        $this->attributes['Nama'] = UnitNormalizer::normalize($value);
    }
}
