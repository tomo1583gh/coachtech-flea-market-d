<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('profile', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        // ユーザー取得
        $user = auth()->user();

        // 更新処理
        $user->name = $request->name;
        $user->zip = $request->zip;
        $user->address = $request->address;
        $user->building = $request->building;

        // 画像処理
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('avatars', 'public');
            $user->image_path = $path;
        }

        $user->save();

        return redirect()->route('top')->with('status', 'プロフィールを更新しました。');
    }
}
