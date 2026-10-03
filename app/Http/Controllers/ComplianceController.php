<?php

namespace App\Http\Controllers;

use App\Domain\Audit\AuditRecorder;
use App\Models\AuditLog;
use App\Models\ClinicalRule;
use App\Models\ControlledDrugRegister;
use App\Models\Generic;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    public function controlledRegister(Request $request)
    {
        $this->authorizePermission('manage-controlled');

        $query = ControlledDrugRegister::query()
            ->with([
                'customer:id,customer_id,name,mobile',
                'medicine:id,medicine_id,name,generic_name,is_controlled,is_narcotic',
                'user:id,name',
                'stock:id,batch_number,expiry_date',
            ])
            ->latest('dispensed_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where(function ($w) use ($q) {
                $w->whereHas('medicine', fn ($m) => $m->where('name', 'like', "%{$q}%")->orWhere('generic_name', 'like', "%{$q}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")->orWhere('mobile', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('dispensed_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('dispensed_at', '<=', $request->get('to'));
        }
        if ($request->filled('source') && in_array($request->get('source'), ['prescription', 'pos', 'manual'], true)) {
            $query->where('source', $request->get('source'));
        }

        return Inertia::render('Compliance/Register', [
            'rows' => $query->limit(200)->get(),
            'filters' => [
                'q' => (string) $request->get('q', ''),
                'from' => (string) $request->get('from', ''),
                'to' => (string) $request->get('to', ''),
                'source' => (string) $request->get('source', ''),
            ],
            'stats' => [
                'total' => ControlledDrugRegister::count(),
                'today' => ControlledDrugRegister::whereDate('dispensed_at', now()->toDateString())->count(),
                'qty_today' => (int) ControlledDrugRegister::whereDate('dispensed_at', now()->toDateString())->sum('quantity'),
            ],
        ]);
    }

    public function audit(Request $request)
    {
        $this->authorizePermission('view-audit');

        $query = AuditLog::query()->with('user:id,name,email')->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->get('module'));
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->get('action').'%');
        }
        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where(function ($w) use ($q) {
                $w->where('module', 'like', "%{$q}%")
                    ->orWhere('action', 'like', "%{$q}%")
                    ->orWhere('record_type', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        $modules = AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');

        return Inertia::render('Compliance/Audit', [
            'logs' => $query->limit(150)->get(),
            'modules' => $modules,
            'filters' => [
                'q' => (string) $request->get('q', ''),
                'module' => (string) $request->get('module', ''),
                'action' => (string) $request->get('action', ''),
            ],
        ]);
    }

    public function clinicalRules()
    {
        $this->authorizePermission('manage-medicines');

        return Inertia::render('Compliance/ClinicalRules', [
            'rules' => ClinicalRule::with([
                'medicine:id,medicine_id,name',
                'otherMedicine:id,medicine_id,name',
            ])->latest()->get(),
            'medicines' => Medicine::where('status', true)->orderBy('name')->get(['id', 'medicine_id', 'name', 'generic_name']),
            'generics' => Generic::where('is_active', true)->orderBy('name')->limit(800)->get(['id', 'generic_id', 'name', 'segment']),
            'notice' => 'These warnings are text you enter. The system does not treat them as clinical advice. MedEx reference text is informational only.',
        ]);
    }

    public function storeClinicalRule(Request $request)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate([
            'medicine_id' => 'nullable|exists:medicines,medicine_id',
            'other_medicine_id' => 'nullable|exists:medicines,medicine_id',
            'rule_type' => 'required|string|max:32',
            'message' => 'required|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $rule = ClinicalRule::create([
            'medicine_id' => ! empty($data['medicine_id']) ? Medicine::localIdOrFail($data['medicine_id']) : null,
            'other_medicine_id' => ! empty($data['other_medicine_id']) ? Medicine::localIdOrFail($data['other_medicine_id']) : null,
            'rule_type' => $data['rule_type'],
            'message' => $data['message'],
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
        ]);

        AuditRecorder::record('compliance', $rule, 'rule_created', null, $rule->only(['rule_type', 'message', 'is_active']));

        return back()->with('success', 'Rule saved.');
    }

    public function updateClinicalRule(Request $request, ClinicalRule $clinicalRule)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate([
            'medicine_id' => 'nullable|exists:medicines,medicine_id',
            'other_medicine_id' => 'nullable|exists:medicines,medicine_id',
            'rule_type' => 'required|string|max:32',
            'message' => 'required|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $old = $clinicalRule->only(['rule_type', 'message', 'is_active', 'medicine_id', 'other_medicine_id']);
        $clinicalRule->update([
            'medicine_id' => ! empty($data['medicine_id']) ? Medicine::localIdOrFail($data['medicine_id']) : null,
            'other_medicine_id' => ! empty($data['other_medicine_id']) ? Medicine::localIdOrFail($data['other_medicine_id']) : null,
            'rule_type' => $data['rule_type'],
            'message' => $data['message'],
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $clinicalRule->is_active,
        ]);

        AuditRecorder::record('compliance', $clinicalRule, 'rule_updated', $old, $clinicalRule->only(['rule_type', 'message', 'is_active']));

        return back()->with('success', 'Rule updated.');
    }

    public function toggleClinicalRule(ClinicalRule $clinicalRule)
    {
        $this->authorizePermission('manage-medicines');
        $clinicalRule->update(['is_active' => ! $clinicalRule->is_active]);
        AuditRecorder::record('compliance', $clinicalRule, 'rule_toggled', null, ['is_active' => $clinicalRule->is_active]);

        return back()->with('success', $clinicalRule->is_active ? 'Rule activated.' : 'Rule deactivated.');
    }

    public function destroyClinicalRule(ClinicalRule $clinicalRule)
    {
        $this->authorizePermission('manage-medicines');
        AuditRecorder::record('compliance', $clinicalRule, 'rule_deleted', $clinicalRule->only(['rule_type', 'message']), null);
        $clinicalRule->delete();

        return back()->with('success', 'Rule deleted.');
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->check() && auth()->user()->can($permission), 403);
    }
}
