<?php

namespace App\Domain\Hrm;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PayrollService
{
    public function generateRun(int $year, int $month, ?int $actorId = null): PayrollRun
    {
        if (PayrollRun::query()->where('year', $year)->where('month', $month)->exists()) {
            throw new RuntimeException('Payroll run already exists for this period.');
        }

        $employees = $this->eligibleEmployees();
        if ($employees->isEmpty()) {
            throw new RuntimeException('No active employees with a salary are available. Add employees and set salary first.');
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $workingDays = $this->countWorkingDays($periodStart, $periodStart->copy()->endOfMonth());

        $run = DB::transaction(function () use ($year, $month, $actorId, $employees, $periodStart, $workingDays) {
            $run = PayrollRun::query()->create([
                'run_number' => sprintf('PAY-%d%02d-%s', $year, $month, Str::upper(Str::random(4))),
                'year' => $year,
                'month' => $month,
                'period_label' => $periodStart->format('F Y'),
                'status' => 'draft',
                'created_by' => $actorId,
            ]);

            $this->createPayslipsForRun($run, $employees, $workingDays);

            return $run;
        });

        return $run->fresh(['payslips.employee']);
    }

    public function approveRun(PayrollRun $run, ?int $actorId = null): PayrollRun
    {
        if ($run->status !== 'draft') {
            throw new RuntimeException('Only draft payroll runs can be approved.');
        }
        if ($run->payslips()->count() === 0) {
            throw new RuntimeException('Cannot approve a payroll run without payslips.');
        }

        $run->update([
            'status' => 'approved',
            'approved_by' => $actorId,
            'approved_at' => now(),
        ]);

        return $run->fresh();
    }

    public function markPaid(PayrollRun $run): PayrollRun
    {
        if ($run->status !== 'approved') {
            throw new RuntimeException('Only approved payroll runs can be marked paid.');
        }

        $run->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $run->fresh();
    }

    public function updatePayslip(Payslip $payslip, array $data): Payslip
    {
        $run = $payslip->payrollRun;
        if ($run->status !== 'draft') {
            throw new RuntimeException('Payslips can only be edited on draft runs.');
        }

        $base = (float) ($data['base_salary'] ?? $payslip->base_salary);
        $bonus = (float) ($data['bonus'] ?? $payslip->bonus);
        $deductions = (float) ($data['deductions'] ?? $payslip->deductions);

        $payslip->update([
            'base_salary' => $base,
            'bonus' => $bonus,
            'deductions' => $deductions,
            'net_pay' => max(0, $base + $bonus - $deductions),
            'days_worked' => (int) ($data['days_worked'] ?? $payslip->days_worked),
            'notes' => $data['notes'] ?? $payslip->notes,
        ]);

        $this->recalculateRunTotals($run);

        return $payslip->fresh('employee');
    }

    private function eligibleEmployees(): Collection
    {
        return Employee::query()
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->orderBy('full_name')
            ->get();
    }

    private function createPayslipsForRun(PayrollRun $run, Collection $employees, int $workingDays): void
    {
        foreach ($employees as $employee) {
            $baseSalary = (float) $employee->salary;
            Payslip::query()->create([
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'base_salary' => $baseSalary,
                'bonus' => 0,
                'deductions' => 0,
                'net_pay' => $baseSalary,
                'days_worked' => $workingDays,
            ]);
        }

        $this->recalculateRunTotals($run);
    }

    private function recalculateRunTotals(PayrollRun $run): void
    {
        $payslips = $run->payslips()->get();
        $run->update([
            'total_gross' => $payslips->sum(fn (Payslip $p) => (float) $p->base_salary + (float) $p->bonus),
            'total_deductions' => $payslips->sum(fn (Payslip $p) => (float) $p->deductions),
            'total_net' => $payslips->sum(fn (Payslip $p) => (float) $p->net_pay),
        ]);
    }

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        return max(1, $days);
    }
}
