@extends('layouts.app-auth')

@section('content')
<div class="auth-container">
    <h2>ログイン</h2>

    @if (session('status'))
    <div class="error">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">メールアドレス</label>
        <input id="email" type="email" name="email">
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">パスワード</label>
        <input id="password" type="password" name="password">
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <button type="submit" class="btn-red">ログインする</button>
    </form>
    <a href="{{ route('register') }}" class="link">会員登録はこちら</a>
</div>
@endsection