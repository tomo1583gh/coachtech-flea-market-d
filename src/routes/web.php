<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\TopController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// トップページ (商品一覧） ※ログイン不要
Route::get('/', [TopController::class, 'index'])->name('top');

// 商品詳細画面 ※ログイン不要
Route::get('/item/{item_id}', [ProductController::class, 'show'])->name('product.show');

// コメント
Route::post('/item/{item_id}/comment', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comment.store');

// メール認証リンクからのアクセス処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了としてマークする

    return redirect('/mypage/profile'); // 認証後のリダイレクト先（プロフィール設定）
})->middleware(['auth', 'signed'])->name('verification.verify');

// ログイン後リダイレクトの判定ルート
Route::get('/redirect-after-login', RedirectController::class)
    ->middleware(['auth', 'verified']);

// 購入完了 ・ キャンセルページ
Route::get('/checkout/success', [PurchaseController::class, 'checkoutSuccess'])->name('checkout.success');

Route::get('/checkout/cancel', function () {
    return redirect()->route('top')->with('error', '決済がキャンセルされました');
})->name('checkout.cancel');

// ログイン必須のページ
Route::middleware(['auth', 'verified'])->group(function () {
    // プロフィール
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');

    // マイページ(出品・購入)共通ルート
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');

    // 商品出品
    Route::get('/sell', [ProductController::class, 'create'])->name('sell');
    Route::post('/sell', [ProductController::class, 'store'])->name('product.store');

    // 商品購入 (追加)
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])->name('purchase.complete');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // 送付先住所変更ページ
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // いいね機能
    Route::post('/item/{item_id}/favorite', [FavoriteController::class, 'toggle'])->name('favorite.toggle');

    // 購入API処理
    Route::post('/checkout', [PurchaseController::class, 'checkout'])->name('checkout');

});
