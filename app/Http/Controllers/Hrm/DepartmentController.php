<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Hrm/Departments/Index', [
            'departments' => Department::query()->withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32|unique:departments,code',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        Department::create([
            'name' => $data['name'],
            'code' => $data['code'] ?: Str::upper(Str::substr(Str::slug($data['name']), 0, 8)),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:32|unique:departments,code,'.$department->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $department->update($data);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Cannot delete a department with employees.');
        }
        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
