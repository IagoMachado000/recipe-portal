<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'recipe_id',
        'user_id',
        'score',
    ];
}
