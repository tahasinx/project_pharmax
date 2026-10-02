<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformSubscription extends Model
{
    use HasPublicId;

    protected $connection = 'mysql_central';

    protected $fillable = [
        'company_id',
        'platform_plan_id',
        'status',
        'starts_on',
        'ends_on',
        'amount',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on'   => 'date',
        'amount'    => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlatformPlan::class, 'platform_plan_id');
    }
}
