<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class MenuController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $menus = Menu::with('roles')->ordered()->get();
        $roles = Role::all();

        return Inertia::render('Menu/Index', [
            'menus' => $menus,
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('Menu/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'route'      => 'required|string|max:255',
            'icon'       => 'nullable|string|max:255',
            'order'      => 'required|integer|min:0',
            'is_active'  => 'boolean',
            'permission' => 'nullable|string|max:255',
            'roles'      => 'required|array',
            'roles.*'    => 'exists:roles,id',
        ]);

        $menu = Menu::create([
            'name'       => $request->name,
            'route'      => $request->route,
            'icon'       => $request->icon,
            'order'      => $request->order,
            'is_active'  => $request->is_active ?? true,
            'permission' => $request->permission,
        ]);

        $menu->roles()->sync($request->roles);

        return redirect()->route('menus.index')
            ->with('success', 'Menu item created successfully.');
    }

    public function edit(Menu $menu)
    {
        $roles = Role::all();
        $menu->load('roles');

        return Inertia::render('Menu/Edit', [
            'menu'  => $menu,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'route'      => 'required|string|max:255',
            'icon'       => 'nullable|string|max:255',
            'order'      => 'required|integer|min:0',
            'is_active'  => 'boolean',
            'permission' => 'nullable|string|max:255',
            'roles'      => 'required|array',
            'roles.*'    => 'exists:roles,id',
        ]);

        $menu->update([
            'name'       => $request->name,
            'route'      => $request->route,
            'icon'       => $request->icon,
            'order'      => $request->order,
            'is_active'  => $request->is_active ?? true,
            'permission' => $request->permission,
        ]);

        $menu->roles()->sync($request->roles);

        return redirect()->route('menus.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('menus.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    public function toggleStatus(Menu $menu)
    {
        $menu->update([
            'is_active' => ! $menu->is_active,
        ]);

        return redirect()->route('menus.index')
            ->with('success', 'Menu status updated successfully.');
    }
}
