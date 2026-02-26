<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Exception;

class TenantService
{
    /**
     * Switch the current active company for the user.
     */
    public function switchCompany(User $user, int $companyId): void
    {
        // Check if user belongs to this company
        $exists = $user->companies()->where('companies.id', $companyId)->exists();
        
        if (!$exists) {
            throw new Exception("User does not belong to this company.");
        }

        $user->current_company_id = $companyId;
        $user->save();
    }
}
