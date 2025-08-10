@extends('layouts.app')

@section('content')
<div class="sell-container">
    <h2 class="sell-title">商品の出品</h2>

    <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- 商品画像 --}}
        <div class="form-group">
            <label for="image">商品画像</label>
            <input type="file" name="image" id="image" class="upload-input" accept="image/*">
            @error('image')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- カテゴリー --}}
        <h3>カテゴリー</h3>
        <div class="category-area">
            @foreach ($categories as $category)
            <label class="category-tag">
                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                    {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                {{ $category->name }}
            </label>
            @endforeach
            @error('category_ids') <div class="error">{{ $message }}</div> @enderror
        </div>

        {{-- 商品の状態 --}}
        <div class="form-group">
            <label for="state">商品の状態</label>
            <select name="state" id="state">
                <option value="">選択してください</option>
                <option value="new" {{ old('state') === 'new' ? 'selected' : '' }}>良好</option>
                <option value="good" {{ old('state') === 'good' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                <option value="fair" {{ old('state') === 'fair' ? 'selected' : '' }}>やや傷や汚れあり</option>
                <option value="poor" {{ old('state') === 'poor' ? 'selected' : '' }}>状態が悪い</option>

            </select>
            @error('state') <div class="error">{{ $message }}</div> @enderror
        </div>

        {{-- 商品名・説明・価格 --}}
        <div class="form-group">
            <label for="name">商品名</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}">
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="brand">ブランド名</label>
            <input type="text" name="brand" id="brand" value="{{ old('brand') }}">
            @error('brand') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="description">商品の説明</label>
            <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="price">販売価格</label>
            <input type="number" name="price" id="price" placeholder="￥" value="{{ old('price') }}">
            @error('price') <div class="error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-red">出品する</button>
    </form>
</div>
@endsection