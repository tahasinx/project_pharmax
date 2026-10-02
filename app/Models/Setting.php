<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'title',
        'menu_title',
        'address',
        'email',
        'phone',
        'logo',
        'login_background',
        'favicon',
        'language',
        'currency',
        'discount_type',
        'timezone',
        'rtl',
        'footer_text',
        'dead_stock_days',
    ];

    protected $casts = [
        'rtl' => 'boolean',
    ];
}
