<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FilterByTenant;

class PaymentMethod extends BaseModel
{
    use HasFactory, FilterByTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
