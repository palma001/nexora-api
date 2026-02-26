<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FilterByTenant;

class Role extends BaseModel
{
    use SoftDeletes, FilterByTenant;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }
    
    public function users(): HasMany
    {
        // This is tricky because the relationship is on company_user pivot
        // But typically we access roles via User->roles()
        return $this->hasMany(CompanyUser::class);
    }
}
