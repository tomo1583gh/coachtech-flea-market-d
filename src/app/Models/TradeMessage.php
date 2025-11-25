<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'body',
        'image_path',
        'is_read',
    ];

    public function product()
    {
        return $this->belongsTo(product::class);
    }

    public function user()
    {
        return $this->belongsTo(user::class);
    }
}
