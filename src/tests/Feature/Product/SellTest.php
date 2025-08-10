<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private User $user;

    private Category $category1;

    private Category $category2;

    protected function setUp(): void
    {
        parent::setUp();

        // 【準備】ユーザー作成 & ログイン
        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($this->user);

        // 【準備】カテゴリ作成
        $this->category1 = Category::factory()->create(['name' => 'ファッション']);
        $this->category2 = Category::factory()->create(['name' => 'メンズ']);

        // 【準備】ストレージのモック
        Storage::fake('public');
    }

    /** @test */
    public function 商品出荷画面にて必要な情報が保存できる()
    {
        // 【準備】フォームデータ作成
        $formData = [
            'name' => 'テスト商品',
            'description' => 'これはテスト用の商品です。',
            'price' => 3000,
            'state' => 'good',
            'category_ids' => [$this->category1->id, $this->category2->id],
            'image' => UploadedFile::fake()->image('test.jpg'),
        ];

        // 【実行】出品フォームを送信
        $response = $this->post('/sell', $formData);

        // 【検証】バリデーションエラーがないこと
        $response->assertSessionHasNoErrors();

        // 【検証】トップページにリダイレクトされること
        $response->assertRedirect('/');

        // 【検証】DBに商品情報が保存されていること
        $this->assertDatabaseHas('products', [
            'name' => 'テスト商品',
            'price' => 3000,
            'state' => 'good',
            'user_id' => $this->user->id,
        ]);

        // 【検証】画像ファイルが保存されていること
        $product = Product::latest()->first();
        Storage::disk('public')->assertExists($product->image_path);

        // 【検証】中間テーブルにカテゴリが紐付いていること
        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $this->category1->id,
        ]);
        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $this->category2->id,
        ]);
    }
}
