<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformPlan extends Model
{
    protected $connection = 'mysql_central';

    protected $fillable = ['name', 'code', 'monthly_amount', 'currency', 'status', 'features'];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'features' => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(PlatformSubscription::class);
    }
}
