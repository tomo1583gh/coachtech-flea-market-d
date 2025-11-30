<?php

namespace Tests\Feature\Trade;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\TradeMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TradeChatTest extends TestCase
{
  use RefreshDatabase;

  private User $seller;
  private User $buyer;
  private User $other;
  private Product $product;

  protected function setUp(): void
  {
    parent::setUp();

    // 出品者・購入者・その他ユーザー
    $this->seller = User::factory()->create();
    $this->buyer  = User::factory()->create();
    $this->other  = User::factory()->create();

    // 取引中の商品（出品者 / 購入者を紐づけ）
    $this->product = Product::factory()->create([
      'user_id'  => $this->seller->id,
      'buyer_id' => $this->buyer->id,
      'is_sold'  => true, // 取引中/取引済み判定に使っているなら調整
    ]);
  }

  /** @test */
  public function 出品者と購入者は取引チャット画面を閲覧できる()
  {
    foreach ([$this->seller, $this->buyer] as $user) {
      $response = $this->actingAs($user)
        ->get(route('trade.chat.show', $this->product));

      $response->assertStatus(200);
      // view 名・渡している変数名は実装に合わせて変更
      $response->assertViewHas('product', function ($product) {
        return $product->id === $this->product->id;
      });
    }
  }

  /** @test */
  public function 第三者は取引チャット画面を閲覧できない()
  {
    $response = $this->actingAs($this->other)
      ->get(route('trade.chat.show', $this->product));

    $response->assertStatus(403);
  }

  /** @test */
  public function 出品者はメッセージを投稿できる()
  {
    $this->actingAs($this->seller);

    $response = $this->from(route('trade.chat.show', $this->product))
      ->post(route('trade.message.store', $this->product), [
        'body' => 'テストメッセージ',
      ]);

    $response->assertRedirect(route('trade.chat.show', $this->product));

    $this->assertDatabaseHas('trade_messages', [
      'product_id' => $this->product->id,
      'user_id'    => $this->seller->id,
      'body'       => 'テストメッセージ',
    ]);
  }

  /** @test */
  public function メッセージが空だとバリデーションエラーになる()
  {
    $this->actingAs($this->seller);

    $response = $this->from(route('trade.chat.show', $this->product))
      ->post(route('trade.message.store', $this->product), [
        'body' => '',
      ]);

    $response->assertRedirect(route('trade.chat.show', $this->product));
    $response->assertSessionHasErrors('body');

    $this->assertDatabaseMissing('trade_messages', [
      'product_id' => $this->product->id,
      'user_id'    => $this->seller->id,
      'body'       => '',
    ]);
  }

  /** @test */
  public function 自分のメッセージは編集できるが他人のものは編集できない()
  {
    $ownMessage = TradeMessage::factory()->create([
      'product_id' => $this->product->id,
      'user_id'    => $this->seller->id,
      'body'       => 'before',
    ]);

    $othersMessage = TradeMessage::factory()->create([
      'product_id' => $this->product->id,
      'user_id'    => $this->buyer->id,
      'body'       => 'others',
    ]);

    // 自分のメッセージは更新できる
    $this->actingAs($this->seller);

    $response = $this->from(route('trade.chat.show', $this->product))
      ->patch(route('trade.message.update', [
        'product' => $this->product->id,
        'message' => $ownMessage->id,
      ]), [
        'body' => 'after',
      ]);

    $response->assertRedirect(route('trade.chat.show', $this->product));

    $this->assertDatabaseHas('trade_messages', [
      'id'   => $ownMessage->id,
      'body' => 'after',
    ]);

    // 他人のメッセージは 403
    $response = $this->from(route('trade.chat.show', $this->product))
      ->patch(route('trade.message.update', [
        'product' => $this->product->id,
        'message' => $othersMessage->id,
      ]), [
        'body' => 'hacked',
      ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('trade_messages', [
      'id'   => $othersMessage->id,
      'body' => 'hacked',
    ]);
  }


  /** @test */
  public function 自分のメッセージは削除できるが他人のものは削除できない()
  {
    $ownMessage = TradeMessage::factory()->create([
      'product_id' => $this->product->id,
      'user_id'    => $this->seller->id,
      'body'       => 'delete me',
    ]);

    $othersMessage = TradeMessage::factory()->create([
      'product_id' => $this->product->id,
      'user_id'    => $this->buyer->id,
      'body'       => 'keep me',
    ]);

    // 自分のメッセージは削除できる
    $this->actingAs($this->seller);

    $response = $this->from(route('trade.chat.show', $this->product))
      ->delete(route('trade.message.destroy', [
        'product' => $this->product->id,
        'message' => $ownMessage->id,
      ]));

    $response->assertRedirect(route('trade.chat.show', $this->product));

    $this->assertDatabaseMissing('trade_messages', [
      'id' => $ownMessage->id,
    ]);

    // 他人のメッセージは削除できない
    $response = $this->from(route('trade.chat.show', $this->product))
      ->delete(route('trade.message.destroy', [
      'product' => $this->product->id,
      'message' => $othersMessage->id,
    ]));

    $response->assertStatus(403);

    $this->assertDatabaseHas('trade_messages', [
      'id'   => $othersMessage->id,
      'body' => 'keep me',
    ]);
  }
}
