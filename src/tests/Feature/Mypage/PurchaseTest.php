<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private $seller;

    private $buyer;

    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        // 【準備】：出品者と商品
        $this->seller = User::factory()->create([
            'name' => '出品者',
        ]);

        $this->product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'name' => 'テスト商品',
        ]);

        // 【準備】：購入者（メール認証済み）
        $this->buyer = User::factory()->create([
            'name' => '購入者',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function 購入ボタンを押下すると購入が完了する()
    {
        // 【実行】：ログインして購入処理
        $response = $this->actingAs($this->buyer)->post(route('purchase.complete', [
            'item_id' => $this->product->id,
        ]), [
            'payment_method' => 'card',
        ]);

        dump($response->headers->get('Location'));

        // 【検証】：リダイレクトとDB反映
        $response->assertRedirect(route('mypage', ['page' => 'buy']));
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'is_sold' => true,
        ]);
    }

    /** @test */
    public function 購入した商品は商品一覧画面にて_sol_dと表示される()
    {
        // 【実行】：購入処理
        $this->actingAs($this->buyer)->post(route('purchase.complete', [
            'item_id' => $this->product->id,
        ]), [
            'payment_method' => 'convenience',
        ]);

        // 【検証】：一覧ページに「SOLD」と商品名が含まれる
        $response = $this->get(route('top'));
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertSee('SOLD');
    }

    /** @test */
    public function プロフィール購入した商品一覧に表示される()
    {
        // 【実行】：購入処理
        $this->actingAs($this->buyer)->post(route('purchase.complete', [
            'item_id' => $this->product->id,
        ]), [
            'payment_method' => 'card',
        ]);

        // 【検証】：マイページ購入一覧に商品名がある
        $response = $this->get(route('mypage', ['page' => 'buy']));
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }
}
