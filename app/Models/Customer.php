<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'fax',
        'status',
        'date_of_birth',
        'gender',
        'allergies',
        'chronic_medicines',
    ];

    protected $casts = [
        'status'        => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
