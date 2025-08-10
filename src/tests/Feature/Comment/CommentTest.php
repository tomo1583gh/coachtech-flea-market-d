<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
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

        // テスト用ユーザーと商品を事前に作成
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $this->product = Product::factory()->create([
            'name' => 'テスト商品',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function ログイン済みのユーザーはコメントを送信できる()
    {
        // 【実行】
        $response = $this->actingAs($this->user)->post(route('comment.store', [
            'item_id' => $this->product->id,
        ]), [
            'comment' => 'これはテストコメントです。',
        ]);

        // 【検証】
        $response->assertRedirect();
        $response->assertStatus(302);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'body' => 'これはテストコメントです。',
        ]);

        // コメントが表示されること
        $detail = $this->actingAs($this->user)->get(route('product.show', ['item_id' => $this->product->id]));
        $detail->assertSee('これはテストコメントです。');
        $detail->assertSee($this->user->name);
    }

    /** @test */
    public function ログイン前のユーザーはコメントを送信できない()
    {
        $response = $this->post(route('comment.store', [
            'item_id' => $this->product->id,
        ]), [
            'comment' => 'ゲストのコメント',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('comments', [
            'product_id' => $this->product->id,
            'body' => 'ゲストのコメント',
        ]);
    }

    /** @test */
    public function コメントが入力されていない場合、バリデーションメッセージが表示される()
    {
        $response = $this->actingAs($this->user)->post(route('comment.store', [
            'item_id' => $this->product->id,
        ]), [
            'comment' => '',
        ]);

        $response->assertRedirect(route('product.show', ['item_id' => $this->product->id]));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['comment']);

        $this->assertDatabaseMissing('comments', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function コメントが255文字以上の場合、バリデーションメッセージが表示される()
    {
        $longComment = str_repeat('あ', 256);

        $response = $this->actingAs($this->user)->post(route('comment.store', [
            'item_id' => $this->product->id,
        ]), [
            'comment' => $longComment,
        ]);

        $response->assertRedirect(route('product.show', ['item_id' => $this->product->id]));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['comment']);

        $this->assertDatabaseMissing('comments', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'body' => $longComment,
        ]);
    }
}
