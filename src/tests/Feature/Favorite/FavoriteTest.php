<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $user;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通データ
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $this->product = Product::factory()->create([
            'name' => 'テスト商品',
            'price' => 1000,
            'user_id' => User::factory()->create()->id,
            'is_sold' => false,
        ]);
    }

    /** @test */
    public function いいねアイコンを押下することによって、いいねした商品として登録することが出来る()
    {
        // 【前提】：いいねされていない
        $this->assertDatabaseMissing('favorite_product', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // 【実行】：いいね登録処理
        $response = $this->actingAs($this->user)->post(route('favorite.toggle', ['item_id' => $this->product->id]));

        // 【検証】：リダイレクトされる
        $response->assertRedirect();

        // 【検証】：DBに登録される
        $this->assertDatabaseHas('favorite_product', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function 追加済みのアイコンは色が変化する()
    {
        // 【前提】：いいね済みにする
        $this->actingAs($this->user)->post(route('favorite.toggle', ['item_id' => $this->product->id]));

        // 【実行】：詳細ページにアクセス
        $response = $this->actingAs($this->user)->get(route('product.show', ['item_id' => $this->product->id]));

        // 【検証】：ステータスとクラス
        $response->assertStatus(200);
        $response->assertSee('class="like-button favorite"', false); // falseでエスケープ無視
    }

    /** @test */
    public function 再度いいねアイコンを押下することによって、いいねを解除することができる()
    {
        // 【前提】：いいね済み
        $this->actingAs($this->user)->post(route('favorite.toggle', ['item_id' => $this->product->id]));
        $this->assertDatabaseHas('favorite_product', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // 【実行】：再度押下
        $this->actingAs($this->user)->post(route('favorite.toggle', ['item_id' => $this->product->id]));

        // 【検証】：DBから削除されている
        $this->assertDatabaseMissing('favorite_product', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // 【検証】：いいね数が「0」と表示
        $response = $this->actingAs($this->user)->get(route('product.show', ['item_id' => $this->product->id]));
        $response->assertSee('0');
    }
}
