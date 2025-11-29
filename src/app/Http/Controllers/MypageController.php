<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    /**
     * マイページ（出品/購入/取引中）の一覧表示
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ★ ユーザー評価（User のaccessor: average_rating を利用）
        // 評価がない場合...null
        // 評価がある場合...1～5 の整数（round済み）想定
        $rating = $user->average_rating; // ← ここで ?? 0 を外す

        // 評価が1件以上あるかどうか（Blade で使うフラグ）
        $hasRating = !is_null($rating);

        // 念のため 0~5 の範囲に丸めておきたい場合（任意）
        if (!is_null($rating)) {
            if ($rating < 0) $rating = 0;
            if ($rating > 5) $rating = 5;
        }

        // ?page=xxx （デフォルトは 'sell'）
        $page = $request->query('page', 'sell');
        
        // ------------------------------------------
        // ①取引中商品のベースクエリ（タブ横バッジ用）
        // ------------------------------------------
        $tradingBaseQuery = Product::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })

            // ★ 未読メッセージ件数をカウントする
            ->withCount([
                'tradeMessages as unread_message_count' => function ($q) use ($user) {
                    $q->where('is_read', false)
                        ->where('user_id', '!=', $user->id); // 自分以外からのメッセージのみ
                },
            ]);

        // ★ タブ横に出す「合計未読件数」
        $totalUnread = (clone $tradingBaseQuery)->get()->sum('unread_message_count');

        // ------------------------------------
        // ②実際に表示するタブごとのクエリ
        // ------------------------------------
        $query = Product::query();

        if ($page === 'buy') {
            // 購入した商品タブ
            $query->where('buyer_id', $user->id)
                ->orderBy('created_at', 'desc');

        } elseif ($page === 'trading') {
            // 取引中の商品タブ：さっきのベースクエリをそのまま利用
            $query = $tradingBaseQuery->orderBy('updated_at', 'desc');

        } else {
            // 出品した商品タブ（デフォルト）
            $query->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(8);

        return view('mypage', compact(
            'user',
            'products',
            'page',
            'totalUnread',
            'rating',
            'hasRating',
        ));
    }
}
