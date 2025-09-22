<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();

        return Inertia::render('Account/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Account/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'balance'     => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        Account::create([
            'name'        => $request->name,
            'code'        => $request->code,
            'type'        => $request->type,
            'balance'     => $request->balance ?? 0,
            'description' => $request->description,
            'status'      => $request->status ?? true,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(Account $account)
    {
        $account->load(['transactions']);

        return Inertia::render('Account/Show', [
            'account' => $account,
        ]);
    }

    public function edit(Account $account)
    {
        return Inertia::render('Account/Edit', [
            'account' => $account,
        ]);
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'balance'     => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $account->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'type'        => $request->type,
            'balance'     => $request->balance ?? $account->balance,
            'description' => $request->description,
            'status'      => $request->status ?? $account->status,
        ]);

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        // Check if account has transactions
        if ($account->transactions()->count() > 0) {
            return redirect()->route('accounts.index')
                ->with('error', 'Cannot delete account with existing transactions.');
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
