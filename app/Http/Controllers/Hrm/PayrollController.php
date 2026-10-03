<?php

namespace App\Http\Controllers\Hrm;

use App\Domain\Hrm\PayrollService;
use App\Http\Controllers\Controller;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    public function index()
    {
        return Inertia::render('Hrm/Payroll/Index', [
            'runs' => PayrollRun::query()->withCount('payslips')->orderByDesc('year')->orderByDesc('month')->get(),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $run = $this->payroll->generateRun((int) $data['year'], (int) $data['month'], $request->user()?->id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('hrm.payroll.show', $run)->with('success', 'Payroll run generated.');
    }

    public function show(PayrollRun $payroll)
    {
        $payroll->load(['payslips.employee.department', 'creator:id,name', 'approver:id,name']);

        return Inertia::render('Hrm/Payroll/Show', [
            'run' => $payroll,
        ]);
    }

    public function approve(PayrollRun $payroll)
    {
        try {
            $this->payroll->approveRun($payroll, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payroll approved.');
    }

    public function markPaid(PayrollRun $payroll)
    {
        try {
            $this->payroll->markPaid($payroll);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payroll marked as paid.');
    }

    public function updatePayslip(Request $request, Payslip $payslip)
    {
        $data = $request->validate([
            'base_salary' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'days_worked' => 'nullable|integer|min:0|max:31',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->payroll->updatePayslip($payslip, $data);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payslip updated.');
    }
}
