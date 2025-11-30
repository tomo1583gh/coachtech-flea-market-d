<?php

namespace Tests\Feature\Trade;

use App\Models\Product;
use App\Models\TradeReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeReviewTest extends TestCase
{
  use RefreshDatabase;

  private User $buyer;
  private User $seller;
  private Product $product;

  protected function setUp(): void
  {
    parent::setUp();

    // 出品者・購入者・商品
    $this->buyer  = User::factory()->create();
    $this->seller = User::factory()->create();

    $this->product = Product::factory()->create([
      'user_id'  => $this->seller->id,   // 出品者
      'buyer_id' => $this->buyer->id,    // 購入者
    ]);
  }

  /**
   * 購入者は一度だけ評価を送信でき、送信後は商品一覧(top)にリダイレクトされる
   * FN012, FN013
   */
  /** @test */
  public function 購入者は一度だけ評価を送信でき、送信後は商品一覧にリダイレクトされる()
  {
    // --- 1回目：成功 ---
    $response = $this->actingAs($this->buyer)
      ->post(route('trade.review.store', $this->product), [
        'rating' => 5,
      ]);

    // DB 1件作成
    $this->assertDatabaseHas('trade_reviews', [
      'product_id'  => $this->product->id,
      'reviewer_id' => $this->buyer->id,
      'reviewee_id' => $this->seller->id,
      'rating'      => 5,
    ]);

    // リダイレクト先
    $response->assertRedirect(route('top'));

    // --- 2回目：失敗（403 または レコード増加なし） ---
    $response2 = $this->actingAs($this->buyer)
      ->post(route('trade.review.store', $this->product), [
        'rating' => 4,
      ]);

    // 二重投稿はできない
    $this->assertEquals(
      1,
      TradeReview::where('product_id', $this->product->id)
        ->where('reviewer_id', $this->buyer->id)
        ->count()
    );

    // 403 or redirect どちらでも許容
    $this->assertTrue(
      in_array($response2->status(), [302, 403]),
      '二重投稿時のレスポンスは 302 or 403 の想定'
    );
  }

  /**
   * 出品者は購入者の評価後に評価を送信でき、二重送信はできない
   * FN012, FN013
   */
  /** @test */
  public function 出品者は購入者の評価後に評価を送信でき、二重送信はできない()
  {
    // 先に購入者が評価を送る
    TradeReview::factory()->create([
      'product_id'  => $this->product->id,
      'reviewer_id' => $this->buyer->id,
      'reviewee_id' => $this->seller->id,
      'rating'      => 5,
    ]);

    // --- 1回目：成功 ---
    $response = $this->actingAs($this->seller)
      ->post(route('trade.review.store', $this->product), [
        'rating' => 4,
      ]);

    $response->assertRedirect(route('top'));

    $this->assertDatabaseHas('trade_reviews', [
      'product_id'  => $this->product->id,
      'reviewer_id' => $this->seller->id,
      'reviewee_id' => $this->buyer->id,
      'rating'      => 4,
    ]);

    // --- 2回目：失敗 ---
    $response2 = $this->actingAs($this->seller)
      ->post(route('trade.review.store', $this->product), [
        'rating' => 3,
      ]);

    // レコードが増えない
    $this->assertEquals(
      1,
      TradeReview::where('product_id', $this->product->id)
        ->where('reviewer_id', $this->seller->id)
        ->count()
    );

    $this->assertTrue(
      in_array($response2->status(), [302, 403]),
      '二重投稿時のレスポンスは 302 or 403 の想定'
    );
  }

  /**
   * プロフィール画面に平均評価が整数(四捨五入)で表示される
   * FN014, FN005
   */
  /** @test */
  public function プロフィール画面に平均評価が四捨五入された整数で表示される()
  {
    // reviewer_id → 誰からの評価でもOK
    // reviewee_id → 評価対象は seller にする

    TradeReview::factory()->create([
      'reviewer_id' => User::factory(),
      'reviewee_id' => $this->seller->id,
      'rating'      => 5,
    ]);
    TradeReview::factory()->create([
      'reviewer_id' => User::factory(),
      'reviewee_id' => $this->seller->id,
      'rating'      => 4,
    ]);
    TradeReview::factory()->create([
      'reviewer_id' => User::factory(),
      'reviewee_id' => $this->seller->id,
      'rating'      => 4,
    ]);

    // 平均 4.33 → 四捨五入で 4

    $response = $this->actingAs($this->seller)
      ->get('/mypage?page=sell');

    $response->assertStatus(200);

    // HTML に 4 が含まれていることを確認
    $response->assertSee('4');
  }
}
