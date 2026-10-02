<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class HeldBill extends Model
{
    use HasPublicId;

    protected $guarded = [];

    protected $casts = ['payload' => 'array'];
}
