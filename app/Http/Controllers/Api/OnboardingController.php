<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanyService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    protected $companyService;
    protected $tenantService;

    public function __construct(CompanyService $companyService, TenantService $tenantService)
    {
        $this->companyService = $companyService;
        $this->tenantService = $tenantService;
    }

    /**
     * Create initial company setup.
     * 
     * @OA\Post(
     *     path="/api/onboarding/company",
     *     @OA\Parameter(
     *         name="name", in="query", required=true, @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="Company created")
     * )
     */
    public function createCompany(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:3',
            'country' => 'nullable|string|max:100',
        ]);

        $company = $this->companyService->create($request->user(), $request->all());

        return response()->json([
            'message' => 'Company created successfully',
            'company' => $company,
            'user' => $request->user()->fresh() // Return updated user with config_pending = false
        ], 201);
    }

    /**
     * Switch active company.
     */
    public function switchCompany(Request $request): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        try {
            $this->tenantService->switchCompany($request->user(), $request->input('company_id'));
            
            return response()->json([
                'message' => 'Company switched successfully',
                'user' => $request->user()->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
    
    /**
    * Skip company setup (if allowed).
    * Not recommended based on requirements, but useful for 'config pending' logic.
    * Requirement says: "Allow to create immediately OR omit and configure later".
    */
    public function skipWait(): JsonResponse
    {
        // If they omit, they are still in pending state?
        // "If no company: company_config_pending = true, Access limited".
        // So hitting this endpoint might just acknowledge they want to stay pending?
        // Or maybe strictly there is nothing to do here, just don't call createCompany.
        
        return response()->json(['message' => 'Setup skipped. Access remains limited.']);
    }

    /**
     * List user's companies.
     */
    public function listCompanies(Request $request): JsonResponse
    {
        return response()->json($request->user()->companies);
    }
}
