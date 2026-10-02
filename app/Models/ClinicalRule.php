<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class ClinicalRule extends Model
{
    use HasPublicId;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];
}
