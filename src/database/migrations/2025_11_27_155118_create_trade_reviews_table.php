<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');   // どの商品化
            $table->unsignedBigInteger('reviewer_id');  // 評価した人
            $table->unsignedBigInteger('reviewee_id');  // 評価された人
            $table->unsignedTinyInteger('rating');      // 1～5
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewee_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['product_id', 'reviewer_id']); // 同じ人が同じ取引を2回評価できないように
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_reviews');
    }
}
