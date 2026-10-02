<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'invoice_id',
        'branch_id',
        'counter_id',
        'customer_id',
        'date',
        'invoice_no',
        'total_amount',
        'total_tax',
        'previous_due',
        'paid_amount',
        'due_amount',
        'total_discount',
        'invoice_discount',
        'bank_id',
        'user_id',
        'details',
        'payment_type',
        'status',
    ];

    protected $casts = [
        'date'             => 'date',
        'total_amount'     => 'decimal:2',
        'total_tax'        => 'decimal:2',
        'previous_due'     => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'due_amount'       => 'decimal:2',
        'total_discount'   => 'decimal:2',
        'invoice_discount' => 'decimal:2',
        'status'           => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // bank() relation removed (no banks table)

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
