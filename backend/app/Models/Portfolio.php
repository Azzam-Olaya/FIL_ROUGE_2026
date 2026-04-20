<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = ['freelancer_id', 'category_id', 'title', 'description', 'images'];

    protected $casts = [
        'images' => 'array',
    ];

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
