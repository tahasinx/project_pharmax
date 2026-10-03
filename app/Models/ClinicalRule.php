<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalRule extends Model
{
    use HasPublicId;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function otherMedicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'other_medicine_id');
    }
}
