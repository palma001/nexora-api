<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $sales = Sale::whereDate('created_at', $date)
                     ->where('status', 'completed')
                     ->get();

        $totalSales = $sales->sum('total');
        
        $byPaymentMethod = Sale::whereDate('created_at', $date)
                               ->where('status', 'completed')
                               ->select('payment_method', DB::raw('sum(total) as total'), DB::raw('count(*) as count'))
                               ->groupBy('payment_method')
                               ->get();

        return response()->json([
            'date' => $date,
            'total_sales' => $totalSales,
            'by_payment_method' => $byPaymentMethod,
            'sales_count' => $sales->count(),
            'expenses' => 0, // Placeholder
            'profit' => $totalSales, // Placeholder (Revenue - Cost)
            'sales' => $sales // Detail list if needed
        ]);
    }
}
