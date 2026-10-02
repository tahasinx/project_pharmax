<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasPublicId;

    protected $guarded = [];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
