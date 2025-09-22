<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'rtl' => 'boolean',
    ];
}
