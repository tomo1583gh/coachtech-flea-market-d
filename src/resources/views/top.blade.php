@extends('layouts.app')

@section('content')
<div class="tab-area">
    <a href="{{ route('top', ['keyword' => request('keyword')]) }}"
        class="{{ request('page') !== 'mylist' ? 'tab active' : 'tab' }}">
        おすすめ
    </a>

    <a href="{{ route('top', ['page' => 'mylist', 'keyword' => request('keyword')]) }}"
        class="{{ request('page') === 'mylist' ? 'tab active' : 'tab' }}">
        マイリスト
    </a>
</div>

@if(request('keyword'))
<div class="search-result-message">
    <p>「{{ request('keyword') }}」の検索結果</p>
</div>
@endif

<div class="product-list">
    @if($products->isEmpty() && request('page') === 'mylist')
    <p class="empty-message">マイリストはありません。</p>
    @endif

    @foreach ($products as $product)
    <a href="{{ route('product.show', ['item_id' => $product->id]) }}" class="product-card">

        @if ($product->is_sold)
        <div class="sold-label">SOLD</div>
        @endif

        <img src="{{ asset('storage/' . $product->image_path) }}" class="product-image" alt=" {{ $product->name }}">
        <div class="product-name">{{ $product->name }}</div>
        <div class="product-price">￥{{ number_format($product->price) }}</div>
    </a>
    @endforeach
</div>

<div class="pagination-wrapper">
    @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $products->links() }}
    @endif
</div>

@endsection