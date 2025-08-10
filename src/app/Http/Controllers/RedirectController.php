<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = Auth::user();

        // プロフィールが未設定（例：住所が空欄なら未設定とみなす）
        if (! $user->isProfileComplete()) {
            return redirect()->route('profile'); // プロフィール編集へ
        }

        return redirect('/'); // 商品一覧へ
    }
}
