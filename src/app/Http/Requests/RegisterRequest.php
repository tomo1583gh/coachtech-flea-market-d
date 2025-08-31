<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'password_confirmation' => ['required', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください',

            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスの形式で入力してください',

            'password.required' => 'パスワードを入力してください',
            'password.confirmed' => 'パスワードと一致しません',
            'password.min' => 'パスワードは8文字以上で入力してください',

            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min' => '確認用パスワードは8文字以上で入力してください',

        ];
    }
}
