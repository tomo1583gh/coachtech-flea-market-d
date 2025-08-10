<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 【準備】共通のユーザーを作成
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        // 【準備】出品者（他人）の商品を3件作成（2件が「りんご」を含む）
        $productA = Product::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => '青りんご',
        ]);

        $productB = Product::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => '赤いりんご',
        ]);

        $productC = Product::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => 'バナナ',
        ]);

        // 【実行】「りんご」で検索
        $response = $this->actingAs($this->user)->get('/?keyword=りんご');

        // 【検証】部分一致した商品が表示される、そうでない商品は非表示
        $response->assertStatus(200);
        $response->assertSee('青りんご');
        $response->assertSee('赤いりんご');
        $response->assertDontSee('バナナ');
    }

    /** @test */
    public function 検索状態がマイリストでも保持されている()
    {
        // 【準備】マイリストに登録する2商品を作成（1件のみ「りんご」を含む）
        $likedApple = Product::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => '青りんご',
        ]);

        $likedBanana = Product::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => 'バナナ',
        ]);

        $this->user->favorites()->attach([$likedApple->id, $likedBanana->id]);

        // 【実行】マイリスト + keyword=りんご で検索
        $response = $this->actingAs($this->user)->get('/?page=mylist&keyword=りんご');

        // 【検証】「りんご」含む商品は表示、含まない商品は非表示
        $response->assertStatus(200);
        $response->assertSee('青りんご');
        $response->assertDontSee('バナナ');
    }
}
