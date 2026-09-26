<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeldBill extends Model
{
    protected $guarded = [];

    protected $casts = ['payload' => 'array'];
}
