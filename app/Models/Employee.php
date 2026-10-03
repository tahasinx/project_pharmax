<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasPublicId;

    protected $fillable = [
        'employee_id',
        'employee_code',
        'user_id',
        'department_id',
        'full_name',
        'email',
        'phone',
        'job_title',
        'employment_type',
        'status',
        'hire_date',
        'termination_date',
        'salary',
        'address',
        'emergency_contact',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
