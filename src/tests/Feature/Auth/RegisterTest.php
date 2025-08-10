<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected string $route;

    protected function setUp(): void
    {
        parent::setUp();
        $this->route = '/register';
    }

    /** @test */
    public function 名前が入力されていない場合、バリデーションメッセージが表示される()
    {
        // 【準備】
        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 【実行】
        $response = $this->from($this->route)->post($this->route, $data);

        // 【検証】
        $response->assertRedirect($this->route)
            ->assertStatus(302);

        $this->assertEquals('お名前を入力してください', session('errors')->first('name'));
    }

    /** @test */
    public function メールアドレスが入力されていない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from($this->route)->post($this->route, [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect($this->route)
            ->assertStatus(302);

        $this->assertEquals('メールアドレスを入力してください', session('errors')->first('email'));
    }

    /** @test */
    public function パスワードが入力されていない場合にバリデーションメッセージが表示される()
    {
        $response = $this->from($this->route)->post($this->route, [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect($this->route)
            ->assertStatus(302);

        $this->assertEquals('パスワードを入力してください', session('errors')->first('password'));
    }

    /** @test */
    public function パスワードが7文字以下の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from($this->route)->post($this->route, [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertRedirect($this->route)
            ->assertStatus(302);

        $this->assertEquals('パスワードは8文字以上で入力してください', session('errors')->first('password'));
    }

    /** @test */
    public function パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from($this->route)->post($this->route, [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertRedirect($this->route)
            ->assertStatus(302);

        $this->assertEquals('パスワードと一致しません', session('errors')->first('password'));
    }

    /** @test */
    public function 全ての項目が入力されている場合、会員情報が登録され、ログイン画面に遷移される()
    {
        $response = $this->post($this->route, [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/redirect-after-login')
            ->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
