<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\TradeReview;
use App\Mail\TradeCompletedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class TradeReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        // 出品者か購入者以外は弾く
        if ($user->id !== $product->user_id && $user->id !== $product->buyer_id) {
            abort(403);
        }

        // 既に評価済みならスキップ
        $already = TradeReview::where('product_id', $product->id)
            ->where('reviewer_id', $user->id)
            ->exists();

        if ($already) {
            return redirect()
                ->route('top')
                ->with('status', 'この取引はすでに評価済みです。');
        }

        // 評価対象のユーザー（相手側）
        $revieweeId = $product->user_id === $user->id
            ? $product->buyer_id
            : $product->user_id;

        if (!$revieweeId) {
            // buyer_id がまだ入っていない等、異常ケース
            abort(400, '評価対象ユーザーが存在しません。');
        }

        // ★ バリデーション
        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
        ]);

        // ★ レビュー保存
        TradeReview::create([
            'product_id'  => $product->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'rating'      => $validated['rating'],
        ]);

        // 取引完了判定　＋　trade_status 更新
        // 出品者がレビュー済みか？
        $sellerReviewed = TradeReview::where('product_id', $product->id)
            ->where('reviewer_id', $product->user_id)
            ->exists();

        // 購入者がレビュー済みか？
        $buyerReviewed = TradeReview::where('product_id', $product->id)
            ->where('reviewer_id', $product->buyer_id)
            ->exists();

        // ★ 出品者・購入者の両方がレビュー済み　→　取引完了ステータスに更新
        if ($sellerReviewed && $buyerReviewed) {
            $product->trade_status = Product::TRADE_STATUS_COMPLETED;  // ★ ここで completed に
            $product->save();                                          // ★ DB 更新
        }
        
        // ★ 購入者が評価したタイミングで出品者にメール送信
        // 「レビューを書いた人＝購入者」のときだけ送る
        if ($user->id === $product->buyer_id) {

            // 出品者情報を user_id から取得
            $seller = User::find($product->user_id);

            // 出品者情報がちゃんと取れているときだけ送信
            if ($seller && $seller->email) {
                Mail::to($seller->email)
                    ->send(new TradeCompletedMail(
                        $product,            // 取引された商品
                        $user,              // 購入者（buyer）
                        $validated['rating'] // 評価（1～5）
                    ));
            }
        }

        // 平均評価は accessor で毎回計算するので、ここで users テーブルを
        // 更新する必要はありません

        return redirect()
            ->route('top')
            ->with('status', '取引評価を送信しました。ありがとうございました！');
    }
}