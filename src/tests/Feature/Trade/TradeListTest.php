<?php

namespace Tests\Feature\Mypage;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TradeListTest extends TestCase
{
  use RefreshDatabase;

  private User $user;
  private User $other;

  protected function setUp(): void
  {
    parent::setUp();

    $this->user  = User::factory()->create();
    $this->other = User::factory()->create();
  }

  /**
   * マイページの取引中一覧には自分が関係する商品だけが表示される
   */
  /** @test */
  public function mypage_trade_list_shows_only_products_related_to_logged_in_user(): void
  {
    // 自分が「出品者」として関係する商品
    $sellerProduct = Product::factory()->create([
      'name'     => '出品側の商品',
      'user_id'  => $this->user->id,   // 出品者: 自分
      'buyer_id' => $this->other->id,  // 購入者: 別ユーザー
    ]);

    // 自分が「購入者」として関係する商品
    $buyerProduct = Product::factory()->create([
      'name'     => '購入側の商品',
      'user_id'  => $this->other->id,  // 出品者: 別ユーザー
      'buyer_id' => $this->user->id,   // 購入者: 自分
    ]);

    // 自分と無関係な商品
    $unrelatedProduct = Product::factory()->create([
      'name'     => '無関係の商品',
      'user_id'  => User::factory(),   // 出品者: 別ユーザー
      'buyer_id' => User::factory(),   // 購入者: 別ユーザー
    ]);

    // マイページの「取引中」タブを表示（?page=trade）
    $response = $this->actingAs($this->user)
      ->get(route('mypage', [
        'page' => 'trading', // 実装で取引中タブに使っている値
      ]));

    $response->assertStatus(200);

    // 手順どおり：
    // - 関係ある seller/buyer 商品は表示されている
    // - 無関係商品は含まれない
    $response->assertSee('出品側の商品');
    $response->assertSee('購入側の商品');
    $response->assertDontSee('無関係の商品');
  }

  /**
   * 取引中一覧が「新しい順」で並ぶ
   *
   * ※ sort=latest のキー/値は実装に合わせて調整
   */
  public function test_取引中一覧が新しい順で並ぶ(): void
  {
    // 古い取引
    $old = Product::factory()->create([
      'user_id'  => $this->user->id,
      'buyer_id' => $this->other->id,
      'created_at' => Carbon::now()->subDay(),
    ]);

    // 新しい取引
    $new = Product::factory()->create([
      'user_id'  => $this->user->id,
      'buyer_id' => $this->other->id,
      'created_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->user)
      ->get(route('mypage', [
        'page' => 'trade',
        'sort' => 'latest',  // ← 実装側のパラメータに合わせて調整
      ]));

    $response->assertStatus(200);

    // HTML 上の並び順を確認（新しい方が前に来る想定）
    $response->assertSeeInOrder([
      $new->name,
      $old->name,
    ]);
  }
}
