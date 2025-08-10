<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'description',
        'price',
        'state',
        'image_path',
        'user_id',
        'buyer_id',
        'zip',
        'address',
        'building',
        'is_sold',

    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'state' => 'string',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorite_product')->withTimestamps();
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'favorite_product', 'product_id', 'user_id')->withTimestamps();
    }
}
