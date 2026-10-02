<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'medicine_id',
        'purchase_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'min_stock_level',
        'max_stock_level',
        'purchase_price',
        'selling_price',
        'supplier',
        'notes',
        'is_active',
        'branch_id',
        'warehouse_id',
        'supplier_id',
        'manufacturing_date',
        'mrp',
        'free_quantity',
        'status',
        'recalled',
    ];

    protected $casts = [
        'expiry_date'        => 'date',
        'quantity'           => 'integer',
        'min_stock_level'    => 'integer',
        'max_stock_level'    => 'integer',
        'purchase_price'     => 'decimal:2',
        'selling_price'      => 'decimal:2',
        'is_active'          => 'boolean',
        'manufacturing_date' => 'date',
        'mrp'                => 'decimal:2',
        'recalled'           => 'boolean',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplierProfile(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Scope for low stock alerts
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock_level')
            ->where('is_active', true);
    }

    // Scope for expired stock
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
            ->where('is_active', true);
    }

    // Scope for expiring soon (within 30 days)
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->where('is_active', true);
    }

    // Scope for active stock
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check if stock is low
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }

    // Check if stock is expired
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Check if stock is expiring soon
    public function isExpiringSoon($days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    // Get days until expiry
    public function daysUntilExpiry(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }
}
