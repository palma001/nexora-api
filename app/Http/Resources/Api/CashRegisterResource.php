<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for CashRegister.
 */
class CashRegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'company_id'    => $this->company_id,
            'branch_id'     => $this->branch_id,
            'name'          => $this->name,
            'is_active'     => (bool) $this->is_active,
            'branch'        => $this->whenLoaded('branch'), // Or use BranchResource if it exists
            'active_shift'  => new CashRegisterShiftResource($this->whenLoaded('activeShift')),
            'created_at'    => $this->created_at,
        ];
    }
}
