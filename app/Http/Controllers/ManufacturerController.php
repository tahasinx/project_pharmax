<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManufacturerController extends Controller
{
    public function index()
    {
        $manufacturers = Manufacturer::withCount('medicines')->get();

        return Inertia::render('Manufacturer/Index', [
            'manufacturers' => $manufacturers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Manufacturer/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'fax'     => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        Manufacturer::create([
            'name'        => $request->name,
            'mobile'      => $request->mobile,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'fax'         => $request->fax,
            'address'     => $request->address,
            'city'        => $request->city,
            'state'       => $request->state,
            'zip'         => $request->zip,
            'country'     => $request->country,
            'status'      => $request->status ?? true,
        ]);

        return redirect()->route('manufacturers.index')
            ->with('success', 'Manufacturer created successfully.');
    }

    public function show(Manufacturer $manufacturer)
    {
        $manufacturer->load(['medicines.category']);

        return Inertia::render('Manufacturer/Show', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function edit(Manufacturer $manufacturer)
    {
        return Inertia::render('Manufacturer/Edit', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function update(Request $request, Manufacturer $manufacturer)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'fax'     => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $manufacturer->update([
            'name'        => $request->name,
            'mobile'      => $request->mobile,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'fax'         => $request->fax,
            'address'     => $request->address,
            'city'        => $request->city,
            'state'       => $request->state,
            'zip'         => $request->zip,
            'country'     => $request->country,
            'status'      => $request->status ?? $manufacturer->status,
        ]);

        return redirect()->route('manufacturers.index')
            ->with('success', 'Manufacturer updated successfully.');
    }

    public function destroy(Manufacturer $manufacturer)
    {
        // Check if manufacturer has medicines
        if ($manufacturer->medicines()->count() > 0) {
            return redirect()->route('manufacturers.index')
                ->with('error', 'Cannot delete manufacturer with existing medicines.');
        }

        $manufacturer->delete();

        return redirect()->route('manufacturers.index')
            ->with('success', 'Manufacturer deleted successfully.');
    }
}
