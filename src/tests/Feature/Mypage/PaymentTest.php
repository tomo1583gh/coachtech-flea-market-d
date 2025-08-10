<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
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

        // 【準備】: 出品者
        $this->seller = User::factory()->create([
            'name' => '出品者',
        ]);

        // 【準備】: 商品（出品者に紐づけ）
        $this->product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'name' => 'テスト商品',
            'price' => 5000,
        ]);

        // 【準備】: 購入者（メール認証済み）
        $this->buyer = User::factory()->create([
            'name' => '購入者',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function 小計画面で変更が反映される()
    {
        // 【実行】: 購入者として購入画面にアクセス
        $response = $this->actingAs($this->buyer)
            ->get(route('purchase.show', ['item_id' => $this->product->id]));

        // 【検証】: 正常に表示され、支払い方法の選択肢がある
        $response->assertStatus(200);
        $response->assertSee('支払い方法');
        $response->assertSee('カード支払い');
        $response->assertSee('コンビニ支払い');
    }
}
