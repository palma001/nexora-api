<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function create(User $user, array $data): Company
    {
        return DB::transaction(function () use ($user, $data) {
            $company = Company::create([
                'owner_id' => $user->id, // Will be set, but also handled by observer potentially
                'name' => $data['name'],
                'tax_id' => $data['tax_id'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'country' => $data['country'] ?? null,
                'is_active' => true,
                'settings' => $data['settings'] ?? [],
            ]);

            // Assign user as owner in pivot
            // We rely on Observer for Branch and Role creation to keep it decoupled
            // But we must attach the user here or in observer. 
            // Let's do it here to ensure we have the context.
            // Actually, the CompanyObserver is better place to handle "On Created" logic
            // providing a single point of truth.
            
            return $company;
        });
    }
}
