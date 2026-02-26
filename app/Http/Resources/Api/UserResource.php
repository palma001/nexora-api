<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $companyId = $request->header('X-Company-Id') ?? auth()->user()->current_company_id;
        $company = $this->companies->where('id', $companyId)->first();
        $pivot = $company ? $company->pivot : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'role_ids' => $this->rolesInCompany($companyId)->pluck('roles.id')->toArray(),
            'role_id' => $pivot ? $pivot->role_id : null, // Keep for backward compatibility if needed
            'direct_permissions' => $this->directPermissions()->pluck('permissions.id')->toArray(),
            'status' => $pivot ? $pivot->status : 'inactive',
            'is_owner' => $this->ownedCompanies()->where('id', $companyId)->exists(),
        ];
    }
}
