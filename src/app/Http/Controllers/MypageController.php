<?php

namespace App\Http\Controllers;

use App\Models\Product;
// use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page');

        if ($page === 'buy') {
            $products = Product::where('buyer_id', $user->id)->paginate(8);
        } else {
            $products = Product::where('user_id', $user->id)->paginate(8);
        }

        return view('mypage', compact('user', 'products', 'page'));
    }
}
