<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class TenderPayment extends Model
{
    use HasPublicId;

    protected $guarded = [];

    protected $casts = ['amount' => 'decimal:2'];
}
