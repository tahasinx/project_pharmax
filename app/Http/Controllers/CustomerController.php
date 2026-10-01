<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    use HasSettingsPagination;

    public function __construct()
    {
    }
    public function index()
    {
        $itemsPerPage = $this->getItemsPerPage();
        $customers = Customer::orderBy('created_at', 'desc')->paginate($itemsPerPage)->withQueryString();

        return Inertia::render('Customer/Index', [
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Customer/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        Customer::create([
            'name'    => $request->name,
            'mobile'  => $request->mobile,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => $request->country,
            'status'  => $request->status ?? true,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'allergies' => $request->allergies,
            'chronic_medicines' => $request->chronic_medicines,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['invoices.items.medicine']);

        return Inertia::render('Customer/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Customer/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $customer->update([
            'name'    => $request->name,
            'mobile'  => $request->mobile,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => $request->country,
            'status'  => $request->status ?? $customer->status,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing invoices.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
