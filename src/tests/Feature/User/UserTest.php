<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private User $user;

    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // 【準備】ユーザーを作成（プロフィール画像は空）
        $this->user = User::factory()->create([
            'name' => '山田 太郎',
            'zip' => '123-4567',
            'address' => '東京都港区芝公園',
            'building' => '芝タワー',
            'image_path' => 'profile_images/dummy.jpg',
            'email_verified_at' => now(),
        ]);

        // 【準備】出品者ユーザーを作成
        $this->seller = User::factory()->create([
            'name' => '出品者ユーザー',
            'email_verified_at' => now(),
        ]);

        // 【準備】ログイン状態にする
        $this->actingAs($this->user);
    }

    /** @test */
    public function 必要な情報が取得できる()
    {
        // 【準備】出品商品（未購入）
        $product1 = Product::factory()->create([
            'user_id' => $this->user->id,
            'is_sold' => false,
            'name' => '出品商品A',
        ]);

        // 【準備】購入商品（is_sold=true, buyer_id指定）
        $product2 = Product::factory()->create([
            'user_id' => $this->seller->id,
            'buyer_id' => $this->user->id,
            'is_sold' => true,
            'name' => '購入商品B',
        ]);

        // 【実行1】プロフィールページ【出品商品一覧】にアクセス
        $response = $this->get('/mypage?page=sell');

        // 【検証1】出品商品が表示されること
        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
        $response->assertSee('出品商品A');
        $response->assertDontSee('購入商品B');

        // 【実行2】プロフィールページ【購入商品一覧】にアクセス
        $response = $this->get('/mypage?page=buy');

        // 【検証1】出品商品が表示されること
        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
        $response->assertSee('購入商品B');
        $response->assertDontSee('出品商品A');

        // 【共通】
        $response->assertSee('profile_images/dummy.jpg');
    }

    /** @test */
    public function 変更項目が初期値として過去設定されていること()
    {
        // 【実行】プロフィール編集画面にアクセス
        $response = $this->get('/mypage/profile');

        // 【検証】各フィールドが反映されているか確認
        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
        $response->assertSee('123-4567');
        $response->assertSee('東京都港区芝公園');
        $response->assertSee('芝タワー');
        $response->assertSee('profile_images/dummy.jpg');
    }
}
