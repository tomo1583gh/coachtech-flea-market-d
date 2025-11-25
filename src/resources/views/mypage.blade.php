@extends('layouts.app')

@section('content')
<div class="mypage-container">

    <div class="mypage-profile">
        <img src="{{ Auth::user()->image_path ? asset('storage/' . Auth::user()->image_path) : asset('images/default-avatar.png') }}" class="mypage-avatar" alt="ユーザー画像">

        <div class="mypage-info">
            <h2>{{ Auth::user()->name }}</h2>
            <a href="{{ route('profile') }}" class="edit-profile-btn">プロフィールを編集</a>
        </div>
    </div>

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
        </a>
    </div>

    <div class="product-list">
    @if ($products->isEmpty())
        @if ($page === 'buy')
            <p class="no-product-message">購入した商品はありません。</p>
        @elseif ($page === 'trading')
            <p class="no-product-message">取引中の商品はありません。</p>
        @else
            <p class="no-product-message">出品した商品はありません。</p>
        @endif
    @else
        {{-- 購入した商品タブ --}}
        @if ($page === 'buy')
            @foreach ($products as $product)
                @if ($product->buyer_id === Auth::id())
                    <a href="{{ route('product.show', ['item_id' => $product->id]) }}" class="product-card">
                        <div class="product-image-wrapper">
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-image">
                            <div class="sold-label">SOLD</div>
                        </div>
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">￥{{ number_format($product->price) }}</div>
                    </a>
                @endif
            @endforeach

        {{-- 取引中の商品タブ --}}
        @elseif ($page === 'trading')
            @foreach ($products as $product)
                {{-- カード全体を「取引チャット」へのリンクにする --}}
                <a href="{{ route('trade.chat.show', ['product' => $product->id]) }}" class="product-card">
                    <div class="product-image-wrapper">
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-image">

                        @if ($product->is_sold)
                            <div class="sold-label">SOLD</div>
                        @endif
                    </div>
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-price">￥{{ number_format($product->price) }}</div>
                </a>
            @endforeach

        {{-- 出品した商品タブ（デフォルト） --}}
        @else
            @foreach ($products as $product)
                @if ($product->user_id === Auth::id())
                    <a href="{{ route('product.show', ['item_id' => $product->id]) }}" class="product-card">
                        <div class="product-image-wrapper">
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-image">

                            @if ($product->is_sold)
                                <div class="sold-label">SOLD</div>
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



    <div class="pagination-wrapper">
        {{ $products->links() }}
    </div>

</div>
@endsection