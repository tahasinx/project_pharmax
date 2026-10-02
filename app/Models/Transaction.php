<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'voucher_no',
        'voucher_type',
        'voucher_date',
        'account_head_code',
        'narration',
        'debit',
        'credit',
        'is_posted',
        'is_opening',
        'is_approved',
        'created_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'debit'        => 'decimal:2',
        'credit'       => 'decimal:2',
        'is_posted'    => 'boolean',
        'is_opening'   => 'boolean',
        'is_approved'  => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_head_code', 'head_code');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
