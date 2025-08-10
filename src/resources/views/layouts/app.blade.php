<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'COACHTECHフリマ')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    @php
    $currentRoute = Route::currentRouteName();
    $excludeHeader = in_array($currentRoute, ['login', 'register', 'verification.notice']);
    @endphp

    @unless($excludeHeader)
    <header class="main-header">
        <div class="header-container">
            <div class="logo-area">
                <a href="{{ route('top') }}">
                    <img src="{{ asset('images/logo.svg') }}" class="logo" alt="ロゴ">
                </a>
            </div>

            <form action="{{ route('top') }}" method="GET" class="search-form">
                <input type="text" name="keyword" placeholder="なにをお探しですか？" value="{{ request('keyword') }}">
            </form>

            <nav class="nav-links">
                @auth
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                <a href="{{ route('mypage') }}">マイページ</a>
                <a href="{{ route('sell') }}" class="btn-sell">出品</a>

                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>
                @endauth

                @guest
                <a href="{{ route('login') }}">ログイン</a>
                <a href="{{ route('register') }}">会員登録</a>
                <a href="{{ route('mypage') }}">マイページ</a>
                <a href="{{ route('sell') }}" class="btn-sell">出品</a>
                @endguest
            </nav>
        </div>
    </header>
    @endunless

    <main>
        @yield('content')
    </main>
</body>

</html>