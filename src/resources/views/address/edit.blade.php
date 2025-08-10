@extends('layouts.app')

@section('content')
<div class="address-edit-container">
    <h2 class="form-title">住所の変更</h2>

    <form method="POST" action="{{ route('purchase.address.update', ['item_id' => $product->id]) }}" class="address-form">
        @csrf

        <div class="form-group">
            <label for="zip">郵便番号</label>
            <input type="text" id="zip" name="zip" value="{{ old('zip', $user->zip) }}">
            @error('zip')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="address">住所</label>
            <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}">
            @if ($errors->has('address'))
            <p class="error">{{ $errors->first('address') }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="building">建物名</label>
            <input type="text" id="building" name="building" value="{{ old('building', $user->building) }}">
            @if ($errors->has('building'))
            <p class="error">{{ $errors->first('building') }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit">更新する</button>
    </form>
</div>
@endsection