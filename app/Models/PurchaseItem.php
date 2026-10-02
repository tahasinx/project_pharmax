<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'batch_id',
        'quantity',
        'rate',
        'discount',
        'total_amount',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'rate'         => 'decimal:2',
        'discount'     => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
