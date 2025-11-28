<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TradeCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Product $product;
    public User $buyer;
    public int $rating;

    /**
     * @param Product $product 取引された商品
     * @param User    $buyer   購入者 
     * @param int     $rating  購入者がつけた評価（1~5)
     */
    public function __construct(Product $product, User $buyer, int $rating)
    {
        $this->product = $product;
        $this->buyer   = $buyer;
        $this->rating  = $rating;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('【COACHTECHフリマ】取引が完了しました')
        ->markdown('emails.trade.completed');
    }
}
