<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\TradeReview;
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
                ->route('top)')
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
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // ★ レビュー保存
        TradeReview::create([
            'product_id'  => $product->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'rating'      => $validated['rating'],
        ]);

        // 平均評価は accessor で毎回計算するので、ここで users テーブルを
        // 更新する必要はありません

        return redirect()
            ->route('top')
            ->with('status', '取引評価を送信しました。ありがとうございました！');
    }
}