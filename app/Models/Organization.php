<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasPublicId;

    protected $guarded = [];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
