<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TradeMessageRequest;
use App\Models\Product;
use App\Models\TradeMessage;
use Illuminate\Support\Facades\Auth;

class TradeChatController extends Controller
{
    /**
     * 取引チャット画面の表示
     * 
     * @param \App\Models\Product $product
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $user = Auth::user();

        // セキュリティチェック：出品者 or 購入者以外はアクセス不可
        if ($product->user_id !== $user->id && $product->buyer_id !== $user->id) {
            abort(403);
        }

        // ★ この時点で「自分以外からの未読メッセージ」を既読にする
        $product->tradeMessages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // サイドバー用：ログインユーザーが関わっている「取引中の商品一覧」
        $tradingProducts = Product::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })
            // 将来的に trade_status カラムを作ったらここで絞り込み
            // ->where('trade_status', 'trading')
            ->withCount('tradeMessages')
            ->orderBy('updated_at', 'desc')
            ->get();

        // 対象商品のメッセージ一覧（古い順）
        $messages = $product->tradeMessages()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('trade.chat', [
            'product'         => $product,
            'user'            => $user,
            'tradingProducts' => $tradingProducts,
            'messages'        => $messages,
        ]);
    }

    /**
     * メッセージ投稿処理
     * 
     * @param \App\Http\Requests\TradeMessageRequest $request
     * @param \App\Models\Product                    $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TradeMessageRequest $request, Product $product)
    {
        $user = Auth::user();

        // セキュリティチェック：出品者 or 購入者以外は投稿不可
        if ($product->user_id !== $user->id && $product->buyer_id !== $user->id) {
            abort(403);
        }

        $imagePath = null;

        // 画像があれば storage/app/public/trades に保存
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trades', 'public');
        }

        TradeMessage::create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'body'       => $request->body,
            'image_path' => $imagePath,
            'is_read'    => false,
        ]);

        // 取引中商品の並び替えに使いたければ、ここで update_at を触るのもアリ
        $product->touch();

        return redirect()
            ->route('trade.chat.show', ['product' => $product->id])
            ->with('status', 'メッセージを送信しました。');
    }

    /**
     * メッセージ編集処理
     */
    public function update(TradeMessageRequest $request, Product $product, TradeMessage $message)
    {
        $user = Auth::user();

        // 商品とメッセージの整合性チェック
        if ($message->product_id !== $product->id) {
            abort(404);
        }

        // 本人以外は編集不可
        if ($message->user_id !== $user->id) {
            abort(403);
        }

        // 今回は本文だけ更新（画像はそのまま）
        $message->update([
            'body' => $request->body,
        ]);

        return redirect()
            ->route('trade.chat.show', ['product' => $product->id])
            ->with('status', 'メッセージを更新しました。');
    }

    /**
     * メッセージ削除処理
     */
    public function destroy(Product $product, TradeMessage $message)
    {
        $user = Auth::user();

        // 商品とメッセージの整合性チェック
        if ($message->product_id !== $product->id) {
            abort(404);
        }

        // 本人以外は削除不可
        if ($message->user_id !== $user->id) {
            abort(403);
        }

        $message->delete();

        return redirect()
            ->route('trade.chat.show', ['product' => $product-> id])
            ->with('status', 'メッセージを削除しました。');
    }
}
