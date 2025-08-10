<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '認証ページ')</title>
    <link rel="stylesheet" href="{{ asset('css/style-auth.css') }}">
</head>

<body>
    <header class="auth-header">
        <div class="auth-header-inner">
            <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" class="auth-logo">
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>