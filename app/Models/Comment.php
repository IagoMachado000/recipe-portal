<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'recipe_id',
        'user_id',
        'body',
    ];
}
