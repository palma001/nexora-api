<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'cost',
        'barcode',
        'stock',
        'stock_min'
    ];

    protected $searchable = [
        'name',
        'description',
        'barcode',
        'price',
        'cost',
        'stock',
        'category.name'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function image()
    {
        return $this->morphOne(Attachment::class, 'attachable')->latest();
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
