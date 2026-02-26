<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasBaseScopes;

abstract class BaseModel extends Model
{
    use HasBaseScopes;
}
