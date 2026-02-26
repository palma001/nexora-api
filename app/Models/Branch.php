<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\FilterByTenant; 

class Branch extends BaseModel
{
    use HasFactory, FilterByTenant, SoftDeletes;
    
    // We will add FilterByTenant trait later when we implement the scope
    // use FilterByTenant; 

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
