@extends('layouts.app')

@section('content')
@php
    // ログインユーザーが出品者かどうか
    $isSeller = $product->user_id === $user->id;
    // 取引相手（出品者なら buyer / 購入者なら user）
    $partner = $isSeller ? $product->buyer : $product->user;
@endphp

<div class="chat-container">

  <div class="chat-layout">
    {{-- =========================
          左：その他の取引 （サイドバー）
        =========================== --}}
      <aside class="chat-sidebar {{ $isSeller ? 'chat-sidebar--seller' : 'chat-sidebar--buyer' }}">
        <p class="chat-sidebar-heading">その他の取引</p>

        {{-- 出品者のときだけ、取引中の商品リストを表示 --}}
        @if ($isSeller)
          @forelse ($tradingProducts as $tradeProduct)
            <a href="{{ route('trade.chat.show', ['product' => $tradeProduct->id]) }}"
              class="chat-sidebar-item {{ $tradeProduct->id === $product->id ? 'is-active' : '' }}">
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
        @endif
      </aside>

      {{-- =================
          右：メインエリア
          =================  --}}
      <div class="chat-main">

        {{-- 取引ヘッダー：「○○さんとの取引画面」＋　取引を完了する ボタン --}}
        <div class="chat-header">
          <div class="chat-header-left">
            <div class="chat-header-avatar">
              @php
                  $partnerAvatar = $partner && $partner->image_path
                      ? asset('storage/' . $partner->image_path)
                      : asset('images/sample.png'); // ダミー画像
              @endphp
              <img src="{{ $partnerAvatar }}" alt="{{ optional($partner)->name ?? 'ユーザー' }}のアイコン">
            </div>
            <p class="chat-header-title">
              「{{ optional($partner)->name ?? 'ユーザー名' }}」さんとの取引画面
            </p>
          </div>

          @if (!$isSeller && !$hasReviewed)
            <div class="chat-header-right">
              <button type="button" class="chat-complete-button" id="openRatingModal">
                取引を完了する
              </button>
            </div>
          @endif
        </div>

        {{-- 商品情報ブロック（）画像＋商品名＋価格 --}}
        <div class="chat-product-row">
          <div class="chat-product-image-wrapper">
            <img src="{{ asset('storage/' . $product->image_path) }}"
              alt="商品画像"
              class="chat-product-image">
          </div>
          <div class="chat-product-info">
            <h3 class="chat-product-name">{{ $product->name }}</h3>
            <p class="chat-product-price">￥{{ number_format($product->price) }}</p>
          </div>
        </div>

        {{-- ここから下はメッセージ一覧＋フォーム（既存ロジックをベース --}}
        
        {{-- メッセージ一覧 --}}
        <div class="chat-messages">
          @forelse ($messages as $msg)
            @php
                $isMine = $msg->user_id === $user->id;

                // このメッセージを書いたユーザー
                $msgUser = $msg->user;
                // アバター画像のURL（なければダミー画像）
                $avatarUrl = ($msgUser && $msgUser->image_path)
                    ? asset('storage/' . $msgUser->image_path)
                    : asset('images/sample.png'); // ダミー画像　パスは環境に合わせて
            @endphp

            <div class="chat-message-row {{ $isMine ? 'chat-message-row--me' : 'chat-message-row--other' }}">
              {{-- 相手側のアイコン（左） --}}
              @unless($isMine)
                <div class="chat-avatar">
                  <img src="{{ $avatarUrl }}" alt="{{ optional($msgUser)->name ?? 'ユーザー' }}のアイコン">
                </div>
              @endunless

              <div class="chat-message {{ $isMine ? 'chat-message--me' : 'chat-message--other' }}">
                <div class="chat-message-header">
                  <span class="chat-message-user">
                    {{ optional($msgUser)->name ?? '不明なユーザー' }}
                  </span>
                  <span class="chat-message-time">
                    {{ $msg->created_at->format('Y-m-d H:i') }}
                  </span>
                </div>

                {{-- 本文 --}}
                @if ($msg->body)
                  <p class="chat-message-body">{{ $msg->body }}</p>
                @endif

                {{-- 画像 --}}
                @if ($msg->image_path)
                  <img src="{{ asset('storage/' . $msg->image_path) }}"
                      alt="メッセージ画像"
                      class="chat-message-image">
                @endif

                {{-- 自分のメッセージだけ編集/削除ボタン --}}
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

              {{-- 自分のアイコン（右） --}}
              @if ($isMine)
                <div class="chat-avatar chat-avatar--me">
                  <img src="{{ $avatarUrl }}" alt="{{ optional($msgUser)->name ?? 'ユーザー' }}のアイコン">
                </div>
              @endif
            </div>
          @empty
            <p class="chat-message-empty">メッセージはまだありません。</p>
          @endforelse
        </div>


        {{-- メッセージ投稿フォーム --}}
        <div class="chat-form-card">
          <form action="{{ route('trade.message.store', ['product' => $product->id]) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

            <div class="chat-form-group">
              <label for="body" class="chat-form-label">取引メッセージを記入してください</label>
              <textarea id="body"
                        name="body"
                        class="chat-form-textarea"
                        rows="3">{{ old('body') }}</textarea>
              @error('body')
                <p class="chat-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="chat-form-footer">
              <label for="image" class="chat-form-image-label">
                画像を追加
                <input id="image"
                      type="file"
                      name="image"
                      class="chat-form-file">
              </label>

              <button type="submit" class="chat-submit-button">
                ▶
              </button>
            </div>

            @error('image')
              <p class="chat-error">{{ $message }}</p>
            @enderror
          </form>
        </div>

        {{-- 取引完了モーダル --}}
        @if (!$isSeller && !$hasReviewed)
        <div id="ratingModal" class="rating-modal">
          <div class="rating-modal__overlay" id="ratingModalOverlay"></div>

          <div class="rating-modal__content">
            <p class="rating-modal__title">取引が完了しました。</p>
            <p class="rating-modal__subtitle">今回の取引相手はどうでしたか？</p>

            <form action="{{ route('trade.review.store', ['product' => $product->id]) }}"
                  method="POST"
                  class="rating-modal__form">
              @csrf

              <div class="rating-stars">
                @for ($i = 1; $i <= 5; $i++)
                  <input type="radio"
                          id="star{{ $i }}"
                          name="rating"
                          value="{{ $i }}"
                          {{ $i == 5 ? 'checked' : '' }}>
                  <label for="star{{ $i }}">★</label>
                @endfor
              </div>

              @error('rating')
                <p class="chat-error">{{ $message }}</p>
              @enderror

              <div class="rating-modal__footer">
                <button type="button" class="rating-modal__close" id="ratingModalClose">
                  キャンセル
                </button>
                <button type="submit" class="rating-modal__submit">
                  送信する
                </button>
              </div>
            </form>
          </div>
        </div>
      @endif

      </div> {{-- /.chat-main --}}
  </div> {{-- /.chat-layout --}}
</div> {{-- /.chat-container --}}
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const openBtn  = document.getElementById('openRatingModal');   // 「取引を完了する」ボタン
  const modal    = document.getElementById('ratingModal');           // モーダル本体
  const overlay  = document.getElementById('ratingModalOverlay');    // 背景
  const closeBtn = document.getElementById('ratingModalClose');      // キャンセルボタン

  // 評価済みなどで要素がないときは何もしない
  if (!openBtn || !modal) return;

  const openModal = () => {
    modal.classList.add('is-open');
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
  };

  openBtn.addEventListener('click', (e) => {
    e.preventDefault();
    openModal();
  });

  if (overlay) {
    overlay.addEventListener('click', closeModal);
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal();
    });
  }
});
</script>
@endsection

