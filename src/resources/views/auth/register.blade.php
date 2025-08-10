@extends('layouts.app-auth')

@section('content')
<div class="auth-container">
    <h2>会員登録</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label for="name">ユーザー名</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" >
        @error('name') <div class="error">{{ $message }}</div> @enderror

        <label for="email">メールアドレス</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" >
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">パスワード</label>
        <input id="password" type="password" name="password" >
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label for="password_confirmation">確認用パスワード</label>
        <input id="password_confirmation" type="password" name="password_confirmation" >

        <button type="submit" class="btn-red">登録する</button>
    </form>
    <a href="{{ route('login') }}" class="link">ログインはこちら</a>
</div>
@endsection