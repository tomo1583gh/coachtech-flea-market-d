@extends('layouts.app')

@section('content')
<div class="mypage-container">

    {{-- ▼ ヘッダー：アイコン + ユーザー名 + 評価★ + 右側にプロフィール編集 --}}
    <div class="mypage-header">
        <div class="mypage-header-left">
            <img
                src="{{ $user->image_path ? asset('storage/' . $user->image_path) : asset('images/default-avatar.png') }}"
                class="mypage-avatar"
                alt="ユーザー画像">

            <div class="mypage-info">
                <h2 class="mypage-username">{{ $user->name }}</h2>

                {{-- ★ 評価（5段階。rating は Controller から渡している想定） --}}
                <div class="mypage-rating">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="rating-star {{ $i <= $rating ? 'rating-star--filled' : '' }}">★</span>
                    @endfor
                </div>
            </div>
        </div>

        <a href="{{ route('profile') }}" class="edit-profile-btn">
            プロフィールを編集
        </a>
    </div>

    {{-- ▼ タブエリア：取引中タブの右に合計未読件数 --}}
    <div class="mypage-tabs">
        <a href="{{ route('mypage', ['page' => 'sell']) }}"
            class="mypage-tab {{ $page === 'sell' ? 'is-active' : '' }}">
            出品した商品
        </a>

        <a href="{{ route('mypage', ['page' => 'buy']) }}"
            class="mypage-tab {{ $page === 'buy' ? 'is-active' : '' }}">
            購入した商品
        </a>

        <a href="{{ route('mypage', ['page' => 'trading']) }}"
            class="mypage-tab {{ $page === 'trading' ? 'is-active' : '' }}">
            取引中の商品
            @if ($totalUnread > 0)
                <span class="tab-unread-badge">{{ $totalUnread }}</span>
            @endif
        </a>
    </div>

    {{-- ▼ 商品一覧 --}}
    <div class="product-list">
        @if ($products->isEmpty())
            {{-- 商品が1件もないとき --}}
            @if ($page === 'buy')
                <p class="no-product-message">購入した商品はありません。</p>
            @elseif ($page === 'trading')
                <p class="no-product-message">取引中の商品はありません。</p>
            @else
                <p class="no-product-message">出品した商品はありません。</p>
            @endif
        @else
            {{-- ★ 購入した商品タブ --}}
            @if ($page === 'buy')
                @foreach ($products as $product)
                    @if ($product->buyer_id === Auth::id())
                        <a href="{{ route('product.show', ['item_id' => $product->id]) }}" class="product-card">
                            <div class="product-image-wrapper">
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                    alt="{{ $product->name }}"
                                    class="product-image">

                                {{-- SOLD ラベル（右上に表示） --}}
                                @if ($product->is_sold)
                                    <div class="sold-label sold-label--right">SOLD</div>
                                @endif
                            </div>
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-price">￥{{ number_format($product->price) }}</div>
                        </a>
                    @endif
                @endforeach

            {{-- ★ 取引中の商品タブ --}}
            @elseif ($page === 'trading')
                @foreach ($products as $product)
                    <a href="{{ route('trade.chat.show', ['product' => $product->id]) }}" class="product-card">
                        <div class="product-image-wrapper">
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                alt="{{ $product->name }}"
                                class="product-image">

                            {{-- SOLD ラベル（右上） --}}
                            @if ($product->is_sold)
                                <div class="sold-label sold-label--right">SOLD</div>
                            @endif

                            {{-- ★ 未読メッセージがある場合、左上に赤バッジ --}}
                            @if (!empty($product->unread_message_count) && $product->unread_message_count > 0)
                                <span class="product-unread-badge">
                                    {{ $product->unread_message_count }}
                                </span>
                            @endif
                        </div>

                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">￥{{ number_format($product->price) }}</div>
                    </a>
                @endforeach

            {{-- ★ 出品した商品タブ（デフォルト） --}}
            @else
                @foreach ($products as $product)
                    @if ($product->user_id === Auth::id())
                        <a href="{{ route('product.show', ['item_id' => $product->id]) }}" class="product-card">
                            <div class="product-image-wrapper">
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                    alt="{{ $product->name }}"
                                    class="product-image">

                                {{-- SOLD ラベル（右上） --}}
                                @if ($product->is_sold)
                                    <div class="sold-label sold-label--right">SOLD</div>
                                @endif
                            </div>
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-price">￥{{ number_format($product->price) }}</div>
                        </a>
                    @endif
                @endforeach
            @endif
        @endif
    </div>

    {{-- ▼ ページネーション（10件中8件 / ページ） --}}
    @if ($products->hasPages())
        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
    @endif

</div>
@endsection
