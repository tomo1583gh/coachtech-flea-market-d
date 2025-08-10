<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通ユーザー作成
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'logout@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function ログアウトができる()
    {
        // 【準備】ログイン状態にする
        $this->actingAs($this->user);

        // 【操作】ログアウト処理を実行（POST /logout）
        $response = $this->post('/logout');

        // 【確認】トップページにリダイレクトされていること
        $response->assertRedirect('/')
            ->assertStatus(302); // 失敗時にエラー調査しやすくする

        // 【確認】ログアウト後は未認証状態
        $this->assertGuest();
    }
}
