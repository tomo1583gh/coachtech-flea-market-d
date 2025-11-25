<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // どの商品との取引か
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 誰のメッセージか
            $table->text('body'); // 本文
            $table->string('image_path')->nullable(); // 画像（任意）
            $table->boolean('is_read')->default(false); // 未読処理（あとで使える）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trad_messages');
    }
}
