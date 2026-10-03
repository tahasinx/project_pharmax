<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Generic extends Model
{
    use HasPublicId;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
