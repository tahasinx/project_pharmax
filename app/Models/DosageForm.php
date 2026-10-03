<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DosageForm extends Model
{
    use HasPublicId;

    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'brand_count' => 'integer',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
