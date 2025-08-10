<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private User $seller;

    private User $buyer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // 【準備】出品者と商品を作成
        $this->seller = User::factory()->create();
        $this->product = Product::factory()->create([
            'user_id' => $this->seller->id,
        ]);

        // 【準備】購入者を作成（初期住所付き）
        $this->buyer = User::factory()->create([
            'zip' => '000-0000',
            'address' => '旧住所',
            'building' => '旧ビル',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->buyer);
    }

    /** @test */
    public function 送付先住所変更画面にて登録した住所が商品購入画面に反映されている()
    {
        // 【実行】送付先住所を変更
        $response = $this->post(route('purchase.address.update', ['item_id' => $this->product->id]), [
            'zip' => '123-4567',
            'address' => '東京都港区新橋',
            'building' => '第一ビル',
        ]);

        // 【検証】リダイレクト先の確認
        $response->assertRedirect(route('purchase.show', ['item_id' => $this->product->id]));

        // 【実行】購入画面へアクセス
        $response = $this->get(route('purchase.show', ['item_id' => $this->product->id]));

        // 【検証】変更後の住所情報が表示されている
        $response->assertSee('123-4567');
        $response->assertSee('東京都港区新橋');
        $response->assertSee('第一ビル');
    }

    /** @test */
    public function 購入した商品に送付先住所が紐付いて登録される()
    {
        // 【準備】購入者の住所を明示的に設定
        $this->buyer->update([
            'zip' => '123-4567',
            'address' => '東京都港区芝公園',
            'building' => '芝タワー',
        ]);

        // 【実行】購入処理を実行
        $response = $this->post(route('purchase.complete', ['item_id' => $this->product->id]), [
            'payment_method' => 'card',
        ]);

        // 【検証】マイページの購入一覧へリダイレクト
        $response->assertRedirect(route('mypage', ['page' => 'buy']));

        // 【検証】productsテーブルに購入者の住所情報が反映されている
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'is_sold' => true,
            'zip' => '123-4567',
            'address' => '東京都港区芝公園',
            'building' => '芝タワー',
        ]);
    }
}
