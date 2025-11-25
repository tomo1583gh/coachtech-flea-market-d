@extends('layouts.app')

@section('content')
<div class="chat-container">
    <h2 class="chat-title">取引チャット</h2>

    <div class="chat-layout">
      {{-- ★ サイドバー（取引中の商品一覧） --}}
      <aside class="chat-sidebar">
        <h3 class="chat-sidebar-title">取引中の商品</h3>

        @forelse ($tradingProducts as $tradeProduct)
            <a href="{{ route('trade.chat.show', ['product' => $tradeProduct->id]) }}"
                class="chat-sidebar-item {{ $tradeProduct->id === $product->id ? 'is-active' : ''}}">
                <div class="chat-sidebar-thumb">
                  <img src="{{ asset('storage/' . $tradeProduct->image_path) }}"
                      alt="{{ $tradeProduct->name }}">
                </div>
                <div class="chat-sidebar-info">
                    <p class="chat-sidebar-name">{{ $tradeProduct->name }}</p>
                    <p class="chat-sidebar-meta">
                        メッセージ {{ $tradeProduct->trade_message_count }} 件
                    </p>
                </div>
            </a>
          @empty
              <p class="chat-sidebar-empty">取引中の商品はありません。</p>
          @endforelse
      </aside>

      {{-- ★ メイン --}}
      <div class="chat-main">
    
          {{-- 商品情報 --}}
          <div class="chat-product-card">
              <h3 class="chat-product-name">{{ $product->name }}</h3>
              <img src="{{ asset('storage/' . $product->image_path) }}"
                  alt="商品画像"
                  class="chat-product-image">
              <p class="chat-product-seller">
                  出品者：{{ optional($product->user)->name ?? '出品者情報がありません' }}
              </p>
          </div>

          {{-- メッセージ一覧 --}}
          <div class="chat-messages">
              @forelse ($messages as $msg)
                  @php
                      $isMine = $msg->user_id === $user->id;
              @endphp

              <div class="chat-message-row {{ $isMine ? 'chat-message-row--me' : 'chat-message-row--other' }}">
                  <div class="chat-message {{ $isMine ? 'chat-message--me' : 'chat-message--other' }}">
                      <div class="chat-message-header">
                          <span class="chat-message-user">
                              {{ optional($msg->user)->name ?? '不明なユーザー' }}</span>
                          <span class="chat-message-time">
                              {{ $msg->created_at->format('Y-m-d H:i') }}
                          </span>
                      </div>

                      {{-- 本文表示：相手のみ --}}
                      @if (!$isMine)
                          <p class="chat-message-body">{{ $msg->body }}</p>
                      @endif

                      @if ($msg->image_path)
                          <img src="{{ asset('storage/' . $msg->image_path) }}"
                              alt="メッセージ画像"
                              class="chat-message-image">
                      @endif

                      {{-- 自分のメッセージだけ編集/削除ボタンを表示 --}}
                      @if ($isMine)
                          <div class="chat-message-actions">
                              {{-- 編集フォーム --}}
                              <form action="{{ route('trade.message.update', ['product' => $product->id, 'message' => $msg->id]) }}"
                                  method="POST"
                                  class="chat-message-edit-form">
                                @csrf
                                @method('PATCH')
                                <textarea name="body"
                                          class="chat-message-edit-textarea"
                                          rows="2">{{ old('body', $msg->body) }}</textarea>
                                <button type="submit" class="chat-edit-button">編集を保存</button>
                              </form>

                              {{-- 削除フォーム --}}
                              <form action="{{ route('trade.message.destroy', ['product' => $product->id, 'message' => $msg->id]) }}"
                                    method="POST"
                                    class="chat-message-delete-form"
                                    onsubmit="return confirm('このメッセージを削除しますか？');">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="chat-delete-button">削除</button>
                              </form>
                          </div>
                        @endif
                  </div>
              </div>
            @empty
              <p class="chat-message-empty">メッセージはまだありません。</p>
            @endforelse
          </div>

        {{-- メッセージ投稿フォーム --}}
        <div class="chat-form-card">
          <form action="{{ route('trade.message.store', ['product' => $product->id]) }}"
            method="Post"
            enctype="multipart/form-data">
            @csrf

            <div class="chat-form-group">
              <label for="body" class="chat-form-label">本文</label>
              <textarea id="body"
                        name="body"
                        class="chat-form-textarea"
                        rows="3">{{ old('body') }}</textarea>
              @error('body')
                  <p class="chat-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="chat-form-group">
                <label for="image" class="chat-form-label">画像（任意）</label>
                <input id="image"
                      type="file"
                      name="image"
                      class="chat-form-file">
                @error('image')
                    <p class="chat-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="chat-form-actions">
              <button type="submit" class="chat-submit-button">送信</button>
            </div>
          </form>
        </div>
    </div>
    @endsection