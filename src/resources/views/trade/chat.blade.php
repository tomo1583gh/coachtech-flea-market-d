@extends('layouts.app')

@section('content')
@php
    // ログインユーザーが出品者かどうか
    $isSeller = $product->user_id === $user->id;
    // 取引相手
    // 自分が出品者なら → 相手は buyer 
    // 自分が購入者なら → 相手は seller
    $partner = $isSeller ? $product->buyer : $product->seller;
@endphp

<div class="chat-container">

  <div class="chat-layout">
    {{-- =========================
          左：その他の取引 （サイドバー）
        =========================== --}}
      <aside class="chat-sidebar {{ $isSeller ? 'chat-sidebar--seller' : 'chat-sidebar--buyer' }}">
        <p class="chat-sidebar-heading">その他の取引</p>

        @forelse ($tradingProducts as $tradeProduct)
          <a href="{{ route('trade.chat.show', ['product' => $tradeProduct->id]) }}"
            class="chat-sidebar-item {{ $tradeProduct->id === $product->id ? 'is-active' : '' }}"> 
          <div class="chat-sidebar-thumb">
            <img src="{{ asset('storage/' . $tradeProduct->image_path) }}"
                alt="{{ $tradeProduct->name }}">
          </div>
          <div class="chat-sidebar-info">
            <p class="chat-sidebar-name">{{ $tradeProduct->name }}</p>
          </div>
        </a>
      @empty
        <p class="chat-sidebar-empty">取引中の商品はありません。</p>
      @endforelse
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
              <button type="button" class="chat-complete-button" id="completeTradeButton">
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

                {{-- 自分のメッセージだけ編集/削除リンク --}}
                @if ($isMine)
                  <div class="chat-message-actions">
                    {{-- 編集リンク --}}
                    <a href="{{ route('trade.message.edit', ['product' => $product->id, 'message' => $msg->id]) }}"
                      class="chat-message-action-link">
                      編集
                    </a>

                    {{-- 削除フォーム --}}
                    <form action="{{ route('trade.message.destroy', ['product' => $product->id, 'message' => $msg->id]) }}"
                          method="POST"
                          class="chat-message-delete-form"
                          onsubmit="return confirm('このメッセージを削除しますか？');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="chat-message-action-link">
                        削除
                      </button>
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
        <form action="{{ route('trade.message.store', ['product' => $product->id]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="chat-input-form">
          @csrf

          <div class="chat-input-row">
            {{-- 左側：テキスト入力＆エラー表示 --}}
            <div class="chat-input-main">
              {{-- 本文・画像のエラーを入力欄の上にまとめて表示 --}}
              @if ($errors->has('body') || $errors->has('image'))
                <div class="form-error-group">
                  @error('body')
                    <p class="form-error--top">{{ $message }}</p>
                  @enderror
                  @error('image')
                    <p class="form-error--top">{{ $message }}</p>
                  @enderror
                </div>
              @endif

            <textarea id="message_body"
                  name="body"
                  class="chat-input-textarea"
                  rows="2"
                  placeholder="取引メッセージを記入してください">{{ old('body') }}</textarea>
            </div>
            
            {{-- 右側：（画像ボタン＋送信ボタン） --}}
            <div class="chat-input-controls">
              {{-- 画像を追加ボタン --}}
              <label class="chat-image-button">
                画像を追加
                <input type="file"
                        name="image"
                        class="chat-image-input">
              </label>

              {{-- 送信ボタン（紙飛行機アイコン） --}}
              <button type="submit" class="chat-send-button" aria-label="送信">
                <svg class="chat-send-icon" viewBox="0 0 24 24" aria-hidden="true">
                  {{-- 外枠（紙飛行機の輪郭） --}}
                  <path d="M3 11.5L21 3L14.5 21L11 13L3 11.5Z"
                    fill="none"
                    stroke="#999999"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                    stroke-linecap="round" />
                  {{-- 中央の折れ線 --}}
                  <path d="M11 13L21 3"
                    fill="none"
                    stroke="#999999"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                    stroke-linecap="round" />
                </svg>
              </button>
            </div>
          </div>
        </form>

        {{-- 取引完了モーダル：購入者＝ボタンから、出品者＝自動表示 --}}
        @if (!$hasReviewed && (!$isSeller || $buyerReviewed))
        <div id="ratingModal"
              class="rating-modal"
              data-auto-open="{{ $isSeller ? '1' : '0' }}">
          <div class="rating-modal__overlay" id="ratingModalOverlay"></div>

          <div class="rating-modal__content">
            <p class="rating-modal__title">取引が完了しました。</p>
            <p class="rating-modal__subtitle">今回の取引相手はどうでしたか？</p>

            <form action="{{ route('trade.review.store', ['product' => $product->id]) }}"
                  method="POST"
                  class="rating-modal__form">
              @csrf

              <div class="rating-stars">
                @for ($i = 5; $i >= 1; $i--)
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
  // ================================
// ② チャット本文の「下書き」保存
// ================================
const textarea = document.getElementById('message_body');
const form     = textarea ? textarea.closest('form') : null;

// 商品ごとにキーを分ける（product_id ベース）
const storageKey = 'trade_draft_body_{{ $product->id }}';

if (textarea) {
  // 1. ページ読み込み時：localStorage から復元
  const saved = localStorage.getItem(storageKey);

  // old('body') が空で、かつ saved がある時だけ上書き
  if (!textarea.value && saved !== null) {
    textarea.value = saved;
  }

  // 2. 入力のたびに localstorage に保存
  textarea.addEventListener('input', () => {
    localStorage.setItem(storageKey, textarea.value);
  });
}

// 3. フォーム送信時：送信したので draft は削除
if (form) {
  form.addEventListener('submit', () => {
    localStorage.removeItem(storageKey);
  });
}

  // ========================
  // ① 評価モーダルの処理
  // ========================
  const modal    = document.getElementById('ratingModal');           // モーダル本体
  if (!modal) return; // モーダルが存在しないページでは何もしない

  const openBtn  = document.getElementById('completeTradeButton');   // 「取引を完了する」ボタン
  const overlay  = document.getElementById('ratingModalOverlay');    // 背景
  const closeBtn = document.getElementById('ratingModalClose');      // キャンセルボタン

  if (modal) {
    // 出品者で自動オープンするかどうか（data-auto-open="1"を見る）
    const autoOpen = modal.dataset.autoOpen === '1';

    const openModal = () => {
      modal.classList.add('is-open');
    };

    const closeModal = () => {
      modal.classList.remove('is-open');
    };

    // 購入者：ボタンクリックで開く
    if (openBtn) {
      openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openModal();
      });
    }

  // 出品者：ページ表示時に自動で開く
  if (autoOpen) {
    openModal();
  }

  // オーバーレイ/キャンセルボタンで閉じる
  if (overlay) {
    closeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal();
    });
  }
}
});
</script>
@endsection

