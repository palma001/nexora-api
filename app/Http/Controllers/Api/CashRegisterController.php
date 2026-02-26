<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * List all cash registers for the current company.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $registers = CashRegister::where('company_id', $companyId)
            ->with(['branch', 'activeShift.opener'])
            ->get();

        return response()->json($registers);
    }

    /**
     * Create a new cash register.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $companyId = auth()->user()->current_company_id;

        $register = CashRegister::create([
            'company_id' => $companyId,
            'branch_id'  => $request->branch_id,
            'name'       => $request->name,
            'is_active'  => true,
        ]);

        return response()->json($register->load('branch'), 201);
    }

    /**
     * Update a cash register.
     */
    public function update(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $cashRegister->update($request->only(['name', 'branch_id', 'is_active']));

        return response()->json($cashRegister->load('branch'));
    }

    /**
     * Delete a cash register (soft delete).
     */
    public function destroy(CashRegister $cashRegister)
    {
        $cashRegister->delete();

        return response()->json(null, 204);
    }
}
