<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::all();

        return Inertia::render('Bank/Index', [
            'banks' => $banks,
        ]);
    }

    public function create()
    {
        return Inertia::render('Bank/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'branch'         => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'swift_code'     => 'nullable|string|max:20',
        ]);

        Bank::create([
            'name'           => $request->name,
            'account_number' => $request->account_number,
            'branch'         => $request->branch,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'address'        => $request->address,
            'swift_code'     => $request->swift_code,
            'status'        => $request->status ?? true,
        ]);

        return redirect()->route('banks.index')
            ->with('success', 'Bank created successfully.');
    }

    public function show(Bank $bank)
    {
        return Inertia::render('Bank/Show', [
            'bank' => $bank,
        ]);
    }

    public function edit(Bank $bank)
    {
        return Inertia::render('Bank/Edit', [
            'bank' => $bank,
        ]);
    }

    public function update(Request $request, Bank $bank)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'branch'         => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'swift_code'     => 'nullable|string|max:20',
        ]);

        $bank->update([
            'name'           => $request->name,
            'account_number' => $request->account_number,
            'branch'         => $request->branch,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'address'        => $request->address,
            'swift_code'     => $request->swift_code,
            'status'        => $request->status ?? $bank->status,
        ]);

        return redirect()->route('banks.index')
            ->with('success', 'Bank updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('banks.index')
            ->with('success', 'Bank deleted successfully.');
    }
}
