<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'medicine_id',
        'type',
        'quantity',
        'unit_price',
        'total_amount',
        'purchase_id',
        'invoice_id',
        'batch_number',
        'expiry_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for purchase transactions
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    // Scope for sale transactions
    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    // Scope for adjustments
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    // Scope for returns
    public function scopeReturns($query)
    {
        return $query->where('type', 'return');
    }
}
