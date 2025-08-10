@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2 class="profile-title">プロフィール設定</h2>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="profile-image-area">
            <img src="{{ asset('storage/' . $user->image_path) }}" class="mypage-avatar" alt="プロフィール画像">
            <label class="image-select-button">
                画像を選択する
                <input type="file" name="image" style="display: none;">
            </label>
        </div>

        <div class="form-group">
            <label for="name">ユーザー名</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}">
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="zip">郵便番号</label>
            <input id="zip" type="text" name="zip" value="{{ old('zip', $user->zip ?? '') }}">
            @error('zip') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="address">住所</label>
            <input id="address" type="text" name="address" value="{{ old('address', $user->address ?? '') }}">
            @error('address') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="building">建物名</label>
            <input id="building" type="text" name="building" value="{{ old('building',$user->building ?? '') }}">
        </div>

        <button type="submit" class="btn-red">更新する</button>
    </form>
</div>
@endsection