<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        return Inertia::render('Hrm/Employees/Index', [
            'employees' => Employee::with([
                'department:id,department_id,name',
                'user:id,name,email',
                'user.roles:id,name',
                'documents' => fn ($q) => $q->latest(),
            ])
                ->withCount('documents')
                ->orderBy('full_name')
                ->get(),
            'departments' => Department::where('status', 'active')->orderBy('name')->get(['id', 'department_id', 'name']),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function create()
    {
        return redirect()->route('hrm.employees.index');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $userId = null;
            if (! empty($data['create_user'])) {
                $user = User::create([
                    'name' => $data['full_name'],
                    'email' => $data['user_email'],
                    'password' => Hash::make($data['user_password']),
                ]);
                if (! empty($data['user_role'])) {
                    $user->syncRoles([$data['user_role']]);
                }
                if (! empty($data['permissions'])) {
                    $user->syncPermissions($data['permissions']);
                }
                $userId = $user->id;
            }

            Employee::create([
                'employee_code' => $data['employee_code'] ?: 'EMP-'.Str::upper(Str::random(6)),
                'user_id' => $userId,
                'department_id' => ! empty($data['department_id']) ? Department::localIdOrFail($data['department_id']) : null,
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? $data['user_email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'employment_type' => $data['employment_type'],
                'status' => $data['status'],
                'hire_date' => $data['hire_date'] ?? null,
                'salary' => $data['salary'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
            ]);
        });

        return back()->with('success', 'Employee created.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'department',
            'user.roles',
            'user.permissions',
            'documents' => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('Hrm/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        return redirect()->route('hrm.employees.index');
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validated($request, $employee);

        $employee->update([
            'employee_code' => $data['employee_code'],
            'department_id' => ! empty($data['department_id']) ? Department::localIdOrFail($data['department_id']) : null,
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'employment_type' => $data['employment_type'],
            'status' => $data['status'],
            'hire_date' => $data['hire_date'] ?? null,
            'salary' => $data['salary'] ?? null,
            'address' => $data['address'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
        ]);

        if ($employee->user) {
            if (! empty($data['user_role'])) {
                $employee->user->syncRoles([$data['user_role']]);
            }
            if (array_key_exists('permissions', $data)) {
                $employee->user->syncPermissions($data['permissions'] ?? []);
            }
            $employee->user->update(['name' => $data['full_name']]);
        }

        return back()->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with('success', 'Employee deleted.');
    }

    public function storeDocument(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('employee-documents/'.$employee->id, 'public');

        $employee->documents()->create([
            'title' => $data['title'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: 0,
            'uploaded_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function destroyDocument(Employee $employee, EmployeeDocument $document)
    {
        if ((int) $document->employee_id !== (int) $employee->id) {
            abort(404);
        }

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Employee $employee = null): array
    {
        $codeRule = $employee
            ? 'required|string|max:32|unique:employees,employee_code,'.$employee->id
            : 'nullable|string|max:32|unique:employees,employee_code';

        return $request->validate([
            'full_name' => 'required|string|max:255',
            'employee_code' => $codeRule,
            'department_id' => 'nullable|exists:departments,department_id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'job_title' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'status' => 'required|in:active,on_leave,terminated',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'create_user' => 'nullable|boolean',
            'user_email' => 'nullable|required_if:create_user,true|email|unique:users,email',
            'user_password' => 'nullable|required_if:create_user,true|string|min:8',
            'user_role' => 'nullable|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);
    }
}
