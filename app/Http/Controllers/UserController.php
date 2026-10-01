<?php

namespace App\Http\Controllers;

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

    public function __construct()
    {
    }

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
        $roles = Role::all();

        return Inertia::render('User/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|lowercase|email|max:255|unique:users',
            'password'    => ['required', 'confirmed', Rules\Password::defaults()],
            'roles'       => 'required|array',
            'roles.*'     => 'exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->roles);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles');

        return Inertia::render('User/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');

        return Inertia::render('User/Edit', [
            'user'  => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'roles'    => 'required|array',
            'roles.*'  => 'exists:roles,name',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
