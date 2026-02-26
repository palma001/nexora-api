<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterShift extends Model
{
    protected $fillable = [
        'cash_register_id',
        'opened_by',
        'closed_by',
        'opening_amount',
        'closing_amount',
        'total_sales',
        'sales_count',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
        'opening_amount'  => 'float',
        'closing_amount'  => 'float',
        'total_sales'     => 'float',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cash_register_shift_id');
    }

    public function isOpen(): bool
    {
        return is_null($this->closed_at);
    }
}
