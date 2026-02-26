<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for CashRegisterShift.
 * Maps the internal model structure to the public API response.
 */
class CashRegisterShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'cash_register_id'    => $this->cash_register_id,
            'opened_by'           => $this->opened_by,
            'closed_by'           => $this->closed_by,
            'opening_amount'      => (float) $this->opening_amount,
            'closing_amount'      => $this->closing_amount !== null ? (float) $this->closing_amount : null,
            'total_sales'         => (float) ($this->total_sales ?? 0),
            'sales_count'         => (int) ($this->sales_count ?? 0),
            'opened_at'           => $this->opened_at->toIso8601String(),
            'closed_at'           => $this->closed_at ? $this->closed_at->toIso8601String() : null,
            'notes'               => $this->notes,
            
            // Relationships
            'cash_register'       => new CashRegisterResource($this->whenLoaded('cashRegister')),
            'opener'              => new UserResource($this->whenLoaded('opener')),
            'closer'              => new UserResource($this->whenLoaded('closer')),

            // Computed / Real-time fields (appended in controller or withSum)
            'current_total_sales' => (float) ($this->current_total_sales ?? $this->current_sales_sum ?? 0),
            'current_sales_count' => (int) ($this->current_sales_count ?? 0),
            'current_cash_sales'  => (float) ($this->current_cash_sales ?? 0),
        ];
    }
}
