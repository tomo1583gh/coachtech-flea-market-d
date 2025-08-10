<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PurchaseController extends Controller
{
    // 購入ページ表示
    public function show($item_id)
    {
        $product = Product::findOrFail($item_id);

        // SOLD商品は購入ページに進めないようにする
        if ($product->is_sold) {
            return redirect()->route('product.show', ['item_id' => $item_id])
                ->with('error', 'この商品はすでに売り切れています。');
        }

        $user = Auth::user();

        return view('purchase', compact('product', 'user'));
    }

    // 購入処理
    public function store(PurchaseRequest $request, $item_id)
    {
        // 商品の取得
        $product = Product::findOrFail($item_id);

        // 購入処理：購入者IDを保存
        $product->buyer_id = Auth::id();
        $product->is_sold = true;

        // ユーザーの住所情報を商品に保存
        $product->zip = Auth::user()->zip;
        $product->address = Auth::user()->address;
        $product->building = Auth::user()->building;

        $product->save();

        return redirect()->route('mypage', ['page' => 'buy'])->with('success', '購入が完了しました。');
    }

    public function editAddress($item_id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($item_id);

        return view('address.edit', compact('user', 'product'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $user = Auth::user();
        $user->zip = $request->zip;
        $user->address = $request->address;
        $user->building = $request->building;
        $user->save();

        return redirect()->route('purchase.show', ['item_id' => $item_id])->with('success', '住所を変更しました。');
    }

    public function checkout(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $product = Product::findOrFail($request->item_id);

        session()->put('purchase_product_id', $product->id);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('checkout.success'), // 決済成功後のURL
            'cancel_url' => route('checkout.cancel'),   // キャンセル時のURL
        ]);

        return redirect($session->url);
    }

    public function checkoutSuccess(Request $request)
    {
        $productId = session()->pull('purchase_product_id'); // セッションから取り出して削除
        $product = Product::find($productId);

        if ($product && ! $product->is_sold) {
            $product->buyer_id = Auth::id();
            $product->is_sold = true;
            $product->save();
        }

        return redirect()->route('top')->with('success', '購入が完了しました');
    }
}
