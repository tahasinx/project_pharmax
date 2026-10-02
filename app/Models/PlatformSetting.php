<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasPublicId;

    protected $connection = 'mysql_central';

    protected $fillable = ['key', 'value'];
}
