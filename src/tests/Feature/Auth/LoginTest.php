<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
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

        // 共通ユーザーを事前に作成
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function メールアドレスが入力されていない場合、バリデーションメッセージが表示される()
    {
        // 【操作】ログインフォームからメールアドレスを空で送信
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        // 【結果の確認】リダイレクトされ、エラーメッセージが表示される
        $response->assertRedirect('/login')
            ->assertStatus(302);

        $this->assertEquals('メールアドレス を入力して下さい。', session('errors')->first('email'));
    }

    /** @test */
    public function パスワードが入力されていない場合、バリデーションメッセージが表示される()
    {
        // 【操作】ログインフォームからパスワードを空で送信
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // 【結果の確認】リダイレクトされ、エラーメッセージが表示される
        $response->assertRedirect('/login')
            ->assertStatus(302);

        $this->assertEquals('パスワード を入力して下さい。', session('errors')->first('password'));
    }

    /** @test */
    public function 入力情報が間違っている場合、バリデーションメッセージが表示される()
    {
        // 【操作】存在しないユーザー情報でログイン試行
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        // 【結果の確認】ログインに失敗し、エラーメッセージが表示される
        $response->assertRedirect('/login')
            ->assertStatus(302);

        $this->assertEquals('ログイン情報が登録されていません。', session('errors')->first('email'));
    }

    /** @test */
    public function 正しい情報が入力された場合、ログイン処理が実行される()
    {
        // 【操作】事前に作成したユーザーでログイン
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        // 【結果の確認】ログイン成功し、リダイレクトされる
        $response->assertRedirect('/redirect-after-login')
            ->assertStatus(302);

        $this->assertAuthenticatedAs($this->user);
    }
}
