<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyUser extends Pivot
{
    protected $table = 'company_user';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'company_id',
        'role_id',
        'is_owner',
        'status',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
