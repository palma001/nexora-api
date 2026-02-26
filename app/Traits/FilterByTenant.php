<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;

trait FilterByTenant
{
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        
        static::creating(function ($model) {
            // Only set company_id if the model is not a User (Users use pivot table)
            if (!($model instanceof \App\Models\User)) {
                if (!$model->company_id && auth()->check()) {
                    $model->company_id = request()->header('X-Company-Id') ?? auth()->user()->current_company_id;
                }
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
