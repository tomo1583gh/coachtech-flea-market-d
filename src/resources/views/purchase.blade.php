@extends('layouts.app')

@section('content')
<div class="purchase-container">
    <div class="purchase-left">
        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-image">
        <h2 class="product-name">{{ $product->name }}</h2>
        <p class="product-price">¥{{ number_format($product->price) }}</p>

        <hr>

        <label for="paymentMethod">支払い方法</label>
        <select id="paymentMethod" name="payment_method" class="payment-select">
            <option value="">選択してください</option>
            <option value="convenience">コンビニ支払い</option>
            <option value="card">カード支払い</option>
        </select>

        @if ($errors->has('payment_method'))
        <p class="error">{{ $errors->first('payment_method') }}</p>
        @endif

        <hr>

        <div class="address-section">
            <p>配送先</p>
            <p>〒{{ $user->zip }}</p>
            <p>{{ $user->address }} {{ $user->building }}</p>
            <a href="{{ route('purchase.address.edit', ['item_id' => $product->id]) }}" class="change-address-link">変更する</a>
        </div>
    </div>

    <div class="purchase-right">
        <div class="summary-box">
            <p>商品代金 <span>¥{{ number_format($product->price) }}</span></p>
            <p>支払い方法 <span id="summaryPayment">未選択</span></p>
        </div>

        <form action="{{ route('checkout') }}" method="POST">
            @csrf
            <input type="hidden" name="item_id" value="{{ $product->id }}">
            <input type="hidden" name="price" value="{{ $product->price }}">
            <!-- <input type="hidden" name="payment_method" value="コンビニ払い"> {{-- ←Stripe Checkoutでは一旦無視OK --}} -->
            <button type="submit" class="btn-red">購入する</button>
        </form>

    </div>
</div>

<script>
    document.getElementById('paymentMethod').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex].text;
        document.getElementById('summaryPayment').textContent = selected;
        document.getElementById('selectedPaymentMethod').value = this.value;
    });
</script>
@endsection