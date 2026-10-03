<?php

namespace App\Http\Controllers;

use App\Domain\Access\PermissionCatalog;
use App\Models\User;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use HasSettingsPagination;

    public function index()
    {
        $itemsPerPage = $this->getItemsPerPage();
        $users = User::with('roles')->paginate($itemsPerPage)->withQueryString();

        return Inertia::render('User/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('User/Create', [
            'roles' => Role::all(),
            'permissionGroups' => PermissionCatalog::groups(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->roles);
        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        return Inertia::render('User/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'permissions']);

        return Inertia::render('User/Edit', [
            'user' => $user,
            'roles' => Role::all(),
            'permissionGroups' => PermissionCatalog::groups(),
            'userPermissions' => $user->getDirectPermissions()->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed|min:8',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncRoles($request->roles);
        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
