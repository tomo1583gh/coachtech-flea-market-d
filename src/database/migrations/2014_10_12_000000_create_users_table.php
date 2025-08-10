<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ユーザー名
            $table->string('email')->unique(); // メールアドレス
            $table->timestamp('email_verified_at')->nullable(); // Fortify用
            $table->string('password'); // ハッシュ済みパスワード
            $table->string('zip')->nullable(); // 郵便番号
            $table->string('address')->nullable(); // 住所
            $table->string('building')->nullable(); // 建物名
            $table->string('image_path')->nullable(); // プロフィール画像のパス
            $table->rememberToken(); // 自動ログイン用トークン
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
