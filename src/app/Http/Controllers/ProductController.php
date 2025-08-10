<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->page === 'mylist') {

            if (Auth::check()) {
                $favoriteIds = Auth::user()->favorites()->pluck('products.id');

                $products = Product::whereIn('id', $favoriteIds)
                    ->where('products.user_id', '!=', Auth::id()) // 自分の出品は除外
                    ->paginate(8);

            } else {
                $products = collect(); // 未ログインは空
            }
        } else {
            $products = Product::query();

            if (Auth::check()) {
                $products->where('user_id', '!=', Auth::id()); // 自分の商品は除外
            }

            $products->where('is_sold', false);
            $products = $products->paginate(8);
        }

        return view('top', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('sell', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        // 商品登録
        $product = new Product;
        $product->user_id = auth()->id();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->state = $request->state;
        $product->brand = $request->brand;

        // 画像の保存処理
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image_path = $imagePath;
        }

        $product->save();

        // カテゴリ紐付け（中間テーブル）
        if ($request->has('category_ids')) {
            $product->categories()->sync($request->category_ids);
        }

        return redirect()->route('top')->with('status', '商品を出品しました。');
    }

    public function show($item_id)
    {
        $product = Product::with(['categories', 'comments.user', 'likedUsers'])
            ->withCount(['comments'])
            ->findOrFail($item_id);

        // ここで再度ユーザーのお気に入り情報を読み込む
        $isFavorited = false;

        if (auth()->check()) {
            // ここで DB から再取得した新鮮な情報を使うことが重要
            $user = auth()->user()->load('favorites');
            $isFavorited = $user->favorites->contains($product->id);
        }

        return view('product.show', compact('product', 'isFavorited'));
    }
}
