<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $connection = 'mysql_central';

    protected $fillable = ['key', 'value'];
}
