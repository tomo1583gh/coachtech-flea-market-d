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

        // クエリパラメータ ?page=xxx （デフォルトは 'sell'）
        $page = $request->query('page', 'sell');

        $query = Product::query();

        if ($page === 'buy') {
            // 購入した商品タブ
            $query->where('buyer_id', $user->id);
        } elseif ($page === 'trading') {
            // 取引中の商品タブ
            // 自分が出品者 or 購入者になっている商品を取得
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            });

            // 将来的に trade_status カラムを作ったらここで絞り込み
            // ->where('trade_status', 'trading');

        } else {
            // 出品した商品タブ（デフォルト）
            $query->where('user_id', $user->id);
        }

        $products = $query->paginate(8);

        return view('mypage', [
            'user'     => $user,
            'products' => $products,
            'page'     => $page,
        ]);
    }
}
