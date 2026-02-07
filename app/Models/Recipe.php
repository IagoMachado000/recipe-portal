<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'ingredients',
        'steps',
        'rating_avg',
        'rating_count',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array'
        ];
    }
}
