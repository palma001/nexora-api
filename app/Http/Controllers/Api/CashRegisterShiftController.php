<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashRegisterShift;
use App\Http\Requests\Api\OpenShiftRequest;
use App\Http\Requests\Api\CloseShiftRequest;
use App\Http\Resources\Api\CashRegisterShiftResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

/**
 * Controller to handle all cash register shift operations.
 * Includes opening, closing, and reporting.
 */
class CashRegisterShiftController extends Controller
{
    /**
     * Allowed roles slugs to open/close shifts.
     */
    private const ALLOWED_ROLES = ['cajero', 'cashier', 'admin', 'administrador', 'super-admin', 'super_admin', 'superadmin'];

    /**
     * Check user has permission to operate shifts.
     * 
     * @return void
     */
    private function authorizeShift(): void
    {
        $user = auth()->user();

        if ($user->is_root) {
            return;
        }

        $role = $user->currentRole();

        if (!$role || !in_array(strtolower($role->slug), self::ALLOWED_ROLES)) {
            abort(403, 'No tienes permiso para abrir o cerrar cajas.');
        }
    }

    /**
     * Get the current user's active shift with real-time sales summary.
     * 
     * @return CashRegisterShiftResource|JsonResponse
     */
    public function myShift()
    {
        $user = auth()->user();
        $companyId = $user->current_company_id;

        $shift = CashRegisterShift::whereNull('closed_at')
            ->where('opened_by', $user->id)
            ->with(['cashRegister.branch', 'opener'])
            ->whereHas('cashRegister', fn($q) => $q->where('company_id', $companyId))
            ->first();

        if ($shift) {
            // Calculate current totals
            $allSales = DB::table('sales')
                ->where('cash_register_shift_id', $shift->id)
                ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as sales_count')
                ->first();

            $cashSales = DB::table('sales')
                ->where('cash_register_shift_id', $shift->id)
                ->where('payment_method', 'cash')
                ->sum('total');

            $shift->current_total_sales = (float) $allSales->total_sales;
            $shift->current_sales_count = (int) $allSales->sales_count;
            $shift->current_cash_sales = (float) $cashSales;
        }

        if (!$shift) {
            return response()->json(null);
        }

        return new CashRegisterShiftResource($shift);
    }

    /**
     * Open a new shift on a given cash register.
     * 
     * @param OpenShiftRequest $request
     * @return CashRegisterShiftResource|JsonResponse
     */
    public function open(OpenShiftRequest $request)
    {
        $this->authorizeShift();

        $user = auth()->user();

        // Check user doesn't already have an open shift
        $existingShift = CashRegisterShift::whereNull('closed_at')
            ->where('opened_by', $user->id)
            ->first();

        if ($existingShift) {
            return response()->json([
                'message' => 'Ya tienes un turno abierto.',
                'shift'   => new CashRegisterShiftResource($existingShift->load(['cashRegister.branch', 'opener'])),
            ], 409);
        }

        // Check the register belongs to this company
        $register = CashRegister::where('id', $request->cash_register_id)
            ->where('company_id', $user->current_company_id)
            ->firstOrFail();

        // Check the register doesn't already have an open shift by someone else
        $registerHasShift = CashRegisterShift::whereNull('closed_at')
            ->where('cash_register_id', $register->id)
            ->exists();

        if ($registerHasShift) {
            return response()->json([
                'message' => 'Esta caja ya tiene un turno abierto.',
            ], 409);
        }

        $shift = CashRegisterShift::create([
            'cash_register_id' => $register->id,
            'opened_by'        => $user->id,
            'opening_amount'   => $request->opening_amount ?? 0,
            'opened_at'        => now(),
            'notes'            => $request->notes,
        ]);

        return new CashRegisterShiftResource($shift->load(['cashRegister.branch', 'opener']));
    }

    /**
     * Close the current user's active shift.
     * 
     * @param CloseShiftRequest $request
     * @param CashRegisterShift $shift
     * @return CashRegisterShiftResource|JsonResponse
     */
    public function close(CloseShiftRequest $request, CashRegisterShift $shift)
    {
        $this->authorizeShift();

        if ($shift->closed_at) {
            return response()->json(['message' => 'Este turno ya fue cerrado.'], 409);
        }

        $user = auth()->user();
        if (!$user->is_root && $shift->opened_by !== $user->id) {
            abort(403, 'No tienes permiso para cerrar este turno.');
        }

        // Calculate totals on closing
        $allSales = DB::table('sales')
            ->where('cash_register_shift_id', $shift->id)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as sales_count')
            ->first();

        $shift->update([
            'closed_by'      => $user->id,
            'closed_at'      => now(),
            'closing_amount' => $request->closing_amount,
            'total_sales'    => $allSales->total_sales,
            'sales_count'    => $allSales->sales_count,
            'notes'          => $request->notes,
        ]);

        return new CashRegisterShiftResource($shift->load(['cashRegister.branch', 'opener', 'closer']));
    }

    /**
     * List shifts with filters for reporting.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->current_company_id;

        $query = CashRegisterShift::with(['cashRegister.branch', 'opener', 'closer'])
            ->whereHas('cashRegister', fn($q) => $q->where('company_id', $companyId));

        // Filter by specific shift ID
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        // Filter by cash register
        if ($request->filled('cash_register_id')) {
            $query->where('cash_register_id', $request->cash_register_id);
        }

        // Filter by opener (cashier)
        if ($request->filled('opened_by')) {
            $query->where('opened_by', $request->opened_by);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->whereHas('cashRegister', fn($q) => $q->where('branch_id', $request->branch_id));
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->where('opened_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('opened_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filter === 'today') {
            $query->whereDate('opened_at', today());
        } elseif ($request->filter === 'yesterday') {
            $query->whereDate('opened_at', today()->subDay());
        }

        // Only closed shifts for reports
        if ($request->boolean('closed_only')) {
            $query->whereNotNull('closed_at');
        }

        // Clone for summary BEFORE reordering and pagination
        $summaryQueryClone = clone $query;

        // Add aggregates to the main query AFTER cloning for summary
        $query->withCount('sales as current_sales_count')
            ->withSum('sales as current_sales_sum', 'total')
            ->withSum(['sales as current_cash_sales' => fn($q) => $q->where('payment_method', 'cash')], 'total');

        $shiftsData = $query->orderBy('opened_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Grouped totals for the filtered query
        $shiftIds = (clone $summaryQueryClone)->select('id');
        
        $byPaymentMethod = DB::table('sales')
            ->whereIn('cash_register_shift_id', $shiftIds)
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $response = $shiftsData->toArray();
        
        // Wrap collection in resource for consistency
        $response['data'] = CashRegisterShiftResource::collection($shiftsData->getCollection());
        
        // Global reconciliation summary
        $globalSummary = (clone $summaryQueryClone)
            ->select([
                DB::raw('SUM(opening_amount) as total_opening'),
                DB::raw('SUM(closing_amount) as total_closing')
            ])
            ->first();

        $totalCashSales = DB::table('sales')
            ->whereIn('cash_register_shift_id', $shiftIds)
            ->where('payment_method', 'cash')
            ->sum('total');

        $response['summary'] = [
            'by_payment_method' => $byPaymentMethod,
            'global' => [
                'total_opening'    => (float) ($globalSummary->total_opening ?? 0),
                'total_closing'    => (float) ($globalSummary->total_closing ?? 0),
                'total_cash_sales' => (float) ($totalCashSales ?? 0),
            ]
        ];

        return response()->json($response);
    }
}
