@component('mail::message')
# 取引が完了しました

{{ $buyer->name }} さんとの取引が完了しました。

---

**商品名**:{{ $product->name }}
**価格**:¥{{ number_format($product->price) }}
**購入者の評価**:{{ $rating }} / 5

---

マイページの取引チャット画面から、取引内容をご確認いただけます。

今後とも COACHTECHフリマ をよろしくお願いいたします。

@endcomponent