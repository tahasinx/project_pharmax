<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'mobile',
        'email',
        'details',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
