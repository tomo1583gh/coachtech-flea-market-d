<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\TradeMessage;
use App\Models\TradeReview;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'zip',
        'address',
        'building',
        'image_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function boughtProducts()
    {
        return $this->hasMany(Product::class, 'buyer_id');
    }

    public function soldProducts()
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'favorite_product', 'user_id', 'product_id')->withTimestamps();
    }

    public function isProfileComplete(): bool
    {
        return ! empty($this->name)
            && ! empty($this->zip)
            && ! empty($this->address);
    }
    
    // 取引チャットメッセージ（自分が送ったもの）
    public function tradeMessage()
    {
        return $this->hasMany(TradeMessage::class);
    }
    
    // 自分が「評価された」レビュー一覧
    public function receivedReviews()
    {
        return $this->hasMany(TradeReview::class, 'reviewee_id');
    }

    // 平均評価（なければ null）
    public function getAverageRatingAttribute()
    {
        $avg = $this->receivedReviews()
            ->whereNotNull('rating')
            ->avg('rating');   // rating カラムの平均値

        // レビューが 1 件もない場合は null になるので 0 にする
        return $avg ? round($avg, 1) : 0;
    }
}
