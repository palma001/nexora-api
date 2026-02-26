<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'fields_schema',
        'credentials',
        'is_active',
    ];

    protected $casts = [
        'fields_schema' => 'array',
        'credentials' => 'array',
        'is_active' => 'boolean',
    ];
}
