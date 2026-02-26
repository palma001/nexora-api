<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = request()->header('X-Company-Id') ?? (Auth::check() ? Auth::user()->current_company_id : null);

        if ($companyId) {
            if ($model instanceof \App\Models\User) {
                $builder->whereHas('companies', function ($query) use ($companyId) {
                    $query->where('companies.id', $companyId);
                });
            } else {
                $builder->where($model->getTable() . '.company_id', $companyId);
            }
        }
    }
}
