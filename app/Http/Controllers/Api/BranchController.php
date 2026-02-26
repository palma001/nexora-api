<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     * Filtered by TenantScope automatically.
     */
    public function index(): JsonResponse
    {
        return response()->json(Branch::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'is_main' => 'boolean',
        ]);

        // If setting as main, need to unset other main branches?
        // Usually only one main branch per company.
        if (!empty($validated['is_main']) && $validated['is_main']) {
            Branch::where('company_id', auth()->user()->current_company_id)
                  ->where('is_main', true)
                  ->update(['is_main' => false]);
        }

        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch): JsonResponse
    {
        // Policy check might be needed here if not covered by middleware/scope
        // For now relying on TenantScope
        return response()->json($branch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string|max:500',
            'is_main' => 'boolean',
        ]);

        if (isset($validated['is_main']) && $validated['is_main']) {
             Branch::where('company_id', auth()->user()->current_company_id)
                  ->where('id', '!=', $branch->id)
                  ->where('is_main', true)
                  ->update(['is_main' => false]);
        }

        $branch->update($validated);

        return response()->json($branch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch): JsonResponse
    {
        // Don't allow deleting the main branch?
        if ($branch->is_main) {
            return response()->json(['message' => 'Cannot delete the main branch.'], 403);
        }

        $branch->delete();
        return response()->json(null, 204);
    }
}
