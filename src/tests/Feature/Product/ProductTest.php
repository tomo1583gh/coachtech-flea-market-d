<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 【準備】共通のログインユーザーを作成（未使用のテストもあるが明示）
        $this->user = User::factory()->create();
    }

    /** @test */
    public function 全商品を取得できる()
    {
        // 【準備】未購入商品を3件作成
        $products = Product::factory()->count(3)->create(['is_sold' => false]);

        // 【実行】商品一覧画面へアクセス（ログイン不要）
        $response = $this->get('/');

        // 【検証】すべての商品名が表示されていること
        foreach ($products as $product) {
            $response->assertSee($product->name);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function 購入済み商品は_sol_dと表示される()
    {
        // 【準備】購入済み商品を作成
        $soldProduct = Product::factory()->create([
            'is_sold' => true,
            'name' => '売れた商品',
        ]);

        // 【実行】一覧画面にアクセス
        $response = $this->get('/');

        // 【検証】商品名と「SOLD」ラベルが表示されていること
        $response->assertSee('売れた商品');
        $response->assertSee('SOLD');
    }

    /** @test */
    public function 自分が出品した商品は表示されない()
    {
        // 【準備】自分の商品と他人の商品を作成
        $otherProduct = Product::factory()->create([
            'name' => '他人の商品',
        ]);

        $myProduct = Product::factory()->create([
            'user_id' => $this->user->id,
            'name' => '自分の商品',
        ]);

        // 【実行】自分でログインして一覧にアクセス
        $response = $this->actingAs($this->user)->get('/');

        // 【検証】自分の商品は表示されないが、他人の商品は表示される
        $response->assertSee('他人の商品');
        $response->assertDontSee('自分の商品');
    }
}
