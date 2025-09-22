<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'category_id',
        'manufacturer_id',
        'generic_name',
        'strength',
        'box_size',
        'product_location',
        'price',
        'manufacturer_price',
        'unit',
        'details',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'manufacturer_price' => 'decimal:2',
        'box_size' => 'integer',
        'status' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    // Get total stock quantity for this medicine
    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->active()->sum('quantity');
    }

    // Check if medicine is low in stock
    public function isLowStock(): bool
    {
        return $this->stocks()->active()->lowStock()->exists();
    }
}
