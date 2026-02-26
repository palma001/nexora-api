<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CashRegisterShift;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'payment_method'  => 'required|string',
            'received_amount' => 'nullable|numeric',
            'items'           => 'required|array',
            'total'           => 'required|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            $user = auth()->user();

            // Find the active shift for this user
            $shift = CashRegisterShift::whereNull('closed_at')
                ->where('opened_by', $user->id)
                ->whereHas('cashRegister', fn($q) => $q->where('company_id', $user->current_company_id))
                ->first();

            $sale = Sale::create([
                'payment_method'          => $request->payment_method,
                'total'                   => $request->total,
                'received_amount'         => $request->received_amount,
                'change_amount'           => ($request->received_amount && $request->received_amount >= $request->total)
                                            ? $request->received_amount - $request->total
                                            : 0,
                'status'                  => 'completed',
                'cash_register_shift_id'  => $shift?->id,
            ]);

            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $item['product_id'] ?? null,
                        'description'=> $item['name'] ?? 'Manual Item',
                        'quantity'   => $item['quantity'] ?? 1,
                        'price'      => $item['price'],
                    ]);
                }
            }

            return $sale->load('items');
        });
    }
}

