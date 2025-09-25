<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManufacturerController extends Controller
{
    use HasSettingsPagination;
    public function index(Request $request)
    {
        $query = Manufacturer::withCount('medicines');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $itemsPerPage = $this->getItemsPerPage();
        $manufacturers = $query->orderBy('name')->paginate($itemsPerPage);

        return Inertia::render('Manufacturer/Index', [
            'manufacturers' => $manufacturers,
            'filters' => $request->only(['search', 'status']),
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
            'address' => 'nullable|string',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'details' => 'nullable|string',
            'status'  => 'nullable|boolean',
        ]);

        Manufacturer::create([
            'name'    => $request->name,
            'address' => $request->address,
            'mobile'  => $request->mobile,
            'email'   => $request->email,
            'details' => $request->details,
            'status'  => $request->status ?? true,
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
            'address' => 'nullable|string',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'details' => 'nullable|string',
            'status'  => 'nullable|boolean',
        ]);

        $manufacturer->update([
            'name'    => $request->name,
            'address' => $request->address,
            'mobile'  => $request->mobile,
            'email'   => $request->email,
            'details' => $request->details,
            'status'  => $request->status ?? $manufacturer->status,
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

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $manufacturers = Manufacturer::where('status', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'mobile']);

        return response()->json($manufacturers);
    }
}
