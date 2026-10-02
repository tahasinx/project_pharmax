<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasPublicId;

    protected $guarded = [];
}
