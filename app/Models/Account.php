<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'balance',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Account $account): void {
            // Keep legacy columns in sync for backward compatibility
            if (isset($account->code)) {
                $account->head_code = $account->code;
            }
            if (isset($account->name)) {
                $account->head_name = $account->name;
            }

            // Map modern type to legacy head_type (A, L, E, I)
            if (isset($account->type)) {
                $map = [
                    'asset' => 'A',
                    'liability' => 'L',
                    'expense' => 'E',
                    'revenue' => 'I', // Income
                    'equity' => 'L',  // closest legacy bucket; adjust if needed
                ];
                $key = strtolower((string) $account->type);
                $account->head_type = $map[$key] ?? strtoupper(substr($key, 0, 1));
            }

            // Sensible defaults for legacy flags if not set
            $account->is_active = $account->is_active ?? true;
            $account->is_transaction = $account->is_transaction ?? false;
            $account->is_gl = $account->is_gl ?? false;
            $account->head_level = $account->head_level ?? 1;
        });
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_head_code', 'head_code');
    }
}
