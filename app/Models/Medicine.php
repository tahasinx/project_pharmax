<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'product_id',
        'name',
        'category_id',
        'manufacturer_id',
        'generic_name',
        'generic_id',
        'medicine_type_id',
        'brand_id',
        'strength',
        'dosage_form',
        'atc_code',
        'sku',
        'requires_prescription',
        'is_controlled',
        'is_antibiotic',
        'is_high_risk',
        'is_refrigerated',
        'is_narcotic',
        'box_size',
        'product_location',
        'price',
        'discount_percent',
        'manufacturer_price',
        'unit',
        'alert_qty',
        'details',
        'image',
        'medex_id',
        'medex_name',
        'qr_code_data',
        'qr_code_type',
        'qr_code_image_path',
        'barcode_data',
        'barcode_type',
        'barcode_image_path',
        'status',
    ];

    protected $casts = [
        'price'                 => 'decimal:2',
        'discount_percent'      => 'decimal:2',
        'manufacturer_price'    => 'decimal:2',
        'alert_qty'             => 'integer',
        'box_size'              => 'integer',
        'status'                => 'boolean',
        'requires_prescription' => 'boolean',
        'is_controlled'         => 'boolean',
        'is_antibiotic'         => 'boolean',
        'is_high_risk'          => 'boolean',
        'is_refrigerated'       => 'boolean',
        'is_narcotic'           => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(Generic::class);
    }

    public function medicineType(): BelongsTo
    {
        return $this->belongsTo(MedicineType::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(MedicineUnit::class)->orderByDesc('factor_to_base');
    }

    public function alternatives()
    {
        if (! $this->generic_id) {
            return collect();
        }

        return static::query()
            ->where('generic_id', $this->generic_id)
            ->where('id', '!=', $this->id)
            ->where('status', true)
            ->get();
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

    // Get available stock batches (active and with quantity > 0)
    public function getAvailableStocks()
    {
        return $this->stocks()->active()->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
    }

    // Check if medicine has sufficient stock for given quantity
    public function hasSufficientStock(int $quantity): bool
    {
        return $this->getTotalStockAttribute() >= $quantity;
    }

    // Get stock transactions for this medicine
    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Get average purchase price from stock
    public function getAveragePurchasePriceAttribute(): float
    {
        $stocks = $this->stocks()->active()->where('quantity', '>', 0)->get();
        if ($stocks->isEmpty()) {
            return $this->manufacturer_price ?? 0;
        }

        $totalValue = $stocks->sum(function ($stock) {
            return $stock->quantity * $stock->purchase_price;
        });
        $totalQuantity = $stocks->sum('quantity');

        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }
}
