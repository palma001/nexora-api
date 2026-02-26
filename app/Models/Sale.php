<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use Auditable;

    protected $fillable = [
        'total',
        'payment_method',
        'received_amount',
        'change_amount',
        'status',
        'cash_register_shift_id',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function shift()
    {
        return $this->belongsTo(CashRegisterShift::class, 'cash_register_shift_id');
    }
}

