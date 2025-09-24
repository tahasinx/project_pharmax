<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'manufacturer_id',
        'purchase_date',
        'purchase_no',
        'details',
        'grand_total',
        'total_tax',
        'total_discount',
        'user_id',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'grand_total' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    // bank() relation removed (no banks table)

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'purchase_id');
    }
}
