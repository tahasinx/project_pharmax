<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformInvoice extends Model
{
    use HasPublicId;

    protected $connection = 'mysql_central';

    protected $fillable = [
        'company_id',
        'platform_subscription_id',
        'number',
        'amount',
        'currency',
        'status',
        'issued_on',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'issued_on' => 'date',
        'paid_at'   => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
