<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasPublicId;

    protected $fillable = [
        'payslip_id',
        'payroll_run_id',
        'employee_id',
        'base_salary',
        'bonus',
        'deductions',
        'net_pay',
        'days_worked',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
