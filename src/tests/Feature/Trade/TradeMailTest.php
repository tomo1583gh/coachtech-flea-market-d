<?php

namespace Tests\Feature\Trade;

use App\Mail\TradeCompletedMail;
use App\Models\Product;
use App\Models\TradeReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TradeMailTest extends TestCase
{
  use RefreshDatabase;

  private User $buyer;
  private User $seller;
  private Product $product;

  protected function setUp(): void
  {
    parent::setUp();

    $this->buyer  = User::factory()->create();
    $this->seller = User::factory()->create();

    $this->product = Product::factory()->create([
      'user_id'  => $this->seller->id,  // 出品者
      'buyer_id' => $this->buyer->id,   // 購入者
      'is_sold'  => true,
    ]);
  }

  /**
   * 購入者が取引完了（評価送信）したときに
   * 出品者にメールが送信される（FN015, FN016）
   */
  /** @test */
  public function buyer_review_triggers_trade_completed_mail()
  {
    Mail::fake();

    // --- 購入者が評価を送信 ---
    $response = $this->actingAs($this->buyer)
      ->post(route('trade.review.store', $this->product), [
        'rating' => 5,
      ]);

    $response->assertRedirect(route('top'));

    // メールが1件だけ送られる
    Mail::assertSent(TradeCompletedMail::class, 1);

    // 中身を検証：宛先が出品者
    Mail::assertSent(TradeCompletedMail::class, function ($mail) {
      return $mail->hasTo($this->seller->email);
    });
  }

  /**
   * 購入者でも出品者でもない第三者が叩いた場合はメールが送信されない
   */
  /** @test */
  public function third_party_cannot_trigger_trade_mail()
  {
    Mail::fake();

    $stranger = User::factory()->create();

    // ★ 関係ないユーザーが叩く（403の想定）
    $response = $this->actingAs($stranger)
      ->post(route('trade.review.store', $this->product), [
        'rating' => 5,
      ]);

    // 403でも302でもOK（実装による）
    $this->assertTrue(
      in_array($response->status(), [403, 302])
    );

    // メールは送信されていない
    Mail::assertNothingSent();
  }
}
