@extends('layouts.app')

@section('content')
<div class="chat-edit-page">
  <h2 class="chat-edit-title">メッセージの編集</h2>

  <div class="chat-edit-product">
    <p class="chat-edit-product-name">{{ $product->name }}</p>
    <p class="chat-edit-product-price">¥{{number_format($product->price) }}</p>
  </div>

  <form method="POST"
      action="{{ route('trade.message.update', ['product' => $product->id, 'message' => $message->id]) }}"
      class="chat-edit-form">
    @csrf
    @method('PATCH')

    <div class="chat-edit-field">
      <label for="body" class="chat-edit-label">メッセージ内容</label>
      <textarea name="body"
                id="body"
                rows="4"
                class="chat-edit-textarea">{{ old('body', $message->body) }}</textarea>
      @error('body')
          <p class="form-error">{{ $message }}</p>
      @enderror
    </div>

    <div class="chat-edit-actions">
      <a href="{{ route('trade.chat.show', ['product' => $product->id]) }}"
        class="chat-edit-cancel">
        キャンセル
      </a>
      <button type="submit" class="chat-edit-submit">
        保存する
      </button>
    </div>
  </form>
</div>
@endsection