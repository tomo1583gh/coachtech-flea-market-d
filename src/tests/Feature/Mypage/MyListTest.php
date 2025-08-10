<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private $user;

    private $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通ユーザー作成
        $this->user = User::factory()->create(['name' => 'ログインユーザー']);
        $this->otherUser = User::factory()->create(['name' => '他ユーザー']);
    }

    /** @test */
    public function いいねした商品だけが表示される()
    {
        // 【準備】
        $likedProduct = Product::factory()->create(['user_id' => $this->otherUser->id, 'name' => 'いいね商品']);
        $notLikedProduct = Product::factory()->create(['user_id' => $this->otherUser->id, 'name' => '非いいね商品']);

        $this->user->favorites()->attach($likedProduct->id);

        // 【実行】
        $response = $this->actingAs($this->user)->get('/?page=mylist');

        // 【検証】
        $response->assertStatus(200);
        $response->assertSee('いいね商品');
        $response->assertDontSee('非いいね商品');
    }

    /** @test */
    public function 購入済み商品は_sol_dと表示される()
    {
        // 【準備】
        $sold = Product::factory()->create(['user_id' => $this->otherUser->id, 'is_sold' => true, 'name' => '購入済']);
        $unsold = Product::factory()->create(['user_id' => $this->otherUser->id, 'is_sold' => false, 'name' => '未購入']);

        $this->user->favorites()->attach([$sold->id, $unsold->id]);

        // 【実行】
        $response = $this->actingAs($this->user)->get('/?page=mylist');

        // 【検証】
        $response->assertStatus(200);
        $response->assertSee('SOLD');
        $response->assertSee('購入済');
        $response->assertSee('未購入');
    }

    /** @test */
    public function 自分が出品した商品は表示されない()
    {
        // 【準備】
        $own = Product::factory()->create(['user_id' => $this->user->id, 'name' => '自分の商品']);
        $other = Product::factory()->create(['user_id' => $this->otherUser->id, 'name' => '他人の商品']);

        $this->user->favorites()->attach([$other->id]);

        // 【実行】
        $response = $this->actingAs($this->user)->get('/?page=mylist');

        // 【検証】
        $response->assertStatus(200);
        $response->assertDontSee('自分の商品');
        $response->assertSee('他人の商品');
    }

    /** @test */
    public function 未認証の場合は何も表示されない()
    {
        // 【実行】
        $response = $this->get('/?page=mylist');

        // 【検証】
        $response->assertStatus(200);
        $response->assertSee('マイリストはありません。');
        $response->assertDontSee('product-card');
    }
}
