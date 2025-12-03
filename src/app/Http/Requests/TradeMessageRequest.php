<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradeMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // 本文：必須・最大400文字
            'body' => ['required', 'string', 'max:400'],

            // 画像：任意・jpeg/pngのみ
            'image' => ['nullable', 'image', 'mimes:jpeg,png'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            // 本文：未入力
            'body.required' => '本文を入力してください',

            // 本文：401文字以上
            'body.max' => '本文は400文字以内で入力してください',

            // 画像：画像以外
            'image.image' => '「.png」または「.jpeg」形式でアップロードしてください',

            // 画像：jpeg/png 以外
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
