<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle($item_id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($item_id);

        $user->load('favorites');

        if ($user->favorites->contains($product->id)) {
            $user->favorites()->detach($product->id);
        } else {
            $user->favorites()->attach($product->id);
        }

        return back(); // 前のページに戻す
    }
}
