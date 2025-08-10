<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        if ($request->page === 'mylist') {
            if (Auth::check()) {
                $favoriteIds = Auth::user()->favorites()->pluck('products.id');

                $products = Product::whereIn('id', $favoriteIds)
                    ->when($request->filled('keyword'), function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->keyword.'%');
                    })
                    ->paginate(8);
            } else {
                $products = collect();
            }
        } else {
            $products = Product::when($userId, function ($query) use ($userId) {
                return $query->where('user_id', '!=', $userId);
            })
                ->when($request->filled('keyword'), function ($query) use ($request) {
                    $query->where('name', 'like', '%'.$request->keyword.'%');
                })
                ->paginate(8);
        }

        return view('top', compact('products'));
    }
}
