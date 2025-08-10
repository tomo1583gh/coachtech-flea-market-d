<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private $user;

    private $product;

    private $category1;

    private $category2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 出品者と商品を作成
        $this->user = User::factory()->create([
            'name' => '出品者',
        ]);

        $this->product = Product::factory()->create([
            'user_id' => $this->user->id,
            'name' => '青りんご',
            'brand' => 'りんご園',
            'price' => 1234,
            'description' => 'おいしい青りんごです。',
            'state' => 1,
        ]);

        // 2. カテゴリ作成 & 紐づけ
        $this->category1 = Category::factory()->create(['name' => '果物']);
        $this->category2 = Category::factory()->create(['name' => '季節限定']);
        $this->product->categories()->attach([$this->category1->id, $this->category2->id]);
    }

    /** @test */
    public function 必要な商品情報とコメントが表示される()
    {
        // 【準備】: コメントユーザー2人
        $commenter1 = User::factory()->create(['name' => 'たろう']);
        $commenter2 = User::factory()->create(['name' => 'はなこ']);

        // 【実行】: コメント作成
        Comment::create([
            'user_id' => $commenter1->id,
            'product_id' => $this->product->id,
            'body' => '美味しそうですね！',
        ]);
        Comment::create([
            'user_id' => $commenter2->id,
            'product_id' => $this->product->id,
            'body' => '買いたいです！',
        ]);

        // 【実行】: 詳細ページへアクセス
        $response = $this->get(route('product.show', ['item_id' => $this->product->id]));

        // 【検証】: ステータス確認 & 内容確認
        $response->assertStatus(200);
        $response->assertSee('青りんご');
        $response->assertSee('りんご園');
        $response->assertSee(number_format(1234));
        $response->assertSee('おいしい青りんごです。');
        $response->assertSee('果物');
        $response->assertSee('季節限定');
        $response->assertSee('コメント (2)');
        $response->assertSee('たろう');
        $response->assertSee('はなこ');
        $response->assertSee('美味しそうですね！');
        $response->assertSee('買いたいです！');
    }

    /** @test */
    public function 複数選択されたカテゴリが表示される()
    {
        // 【実行）: 商品詳細へアクセス
        $response = $this->get(route('product.show', ['item_id' => $this->product->id]));

        // 【検証】: カテゴリ名が表示されている
        $response->assertStatus(200);
        $response->assertSee('果物');
        $response->assertSee('季節限定');
    }
}
