<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJobPostingRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'companyName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tel' => ['required', 'string', 'max:20'],
            'salaryType' => ['required', 'string', 'in:時給,日給'],
            'wage' => ['required', 'integer'],
            'employmentType' => ['required', 'string', 'in:パートタイム,アルバイト'],
            'externalLinkUrl' => ['required', 'url'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
            'postalCode' => ['nullable', 'string', 'max:10'],
            'access' => ['nullable', 'string', 'max:500'],
            
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '求人タイトルは必須です。',
            'title.string' => '求人タイトルは文字列で入力してください。',
            'title.max' => '求人タイトルは255文字以下で入力してください。',
            'companyName.required' => '会社名は必須です。',
            'companyName.string' => '会社名は文字列で入力してください。',
            'companyName.max' => '会社名は255文字以下で入力してください。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'email.max' => 'メールアドレスは255文字以下で入力してください。',
            'tel.required' => '電話番号は必須です。',
            'tel.string' => '電話番号は文字列で入力してください。',
            'tel.max' => '電話番号は20文字以下で入力してください。',
            'salaryType.required' => '給与形態は必須です。',
            'salaryType.string' => '給与形態は文字列で入力してください。',
            'salaryType.in' => '給与形態は「時給」または「日給」で入力してください。',
            'wage.required' => '時給は必須です。',
            'wage.integer' => '時給は整数で入力してください。',
            'employmentType.required' => '雇用形態は必須です。',
            'employmentType.string' => '雇用形態は文字列で入力してください。',
            'employmentType.in' => '雇用形態は「パートタイム」または「アルバイト」で入力してください。',
            'externalLinkUrl.required' => '外部リンクURLは必須です。',
            'externalLinkUrl.url' => '外部リンクURLの形式が正しくありません。',
            'image.required' => '画像は必須です。',
            'image.image' => '画像は画像ファイルでアップロードしてください。',
            'image.mimes' => '画像はjpeg,png,jpg,gif形式でアップロードしてください。',
            'image.max' => '画像は10MB以下でアップロードしてください。',
            'postalCode.string' => '郵便番号は文字列で入力してください。',
            'postalCode.max' => '郵便番号は10文字以下で入力してください。',
            'access.string' => '住所は文字列で入力してください。',
            'access.max' => '住所は500文字以下で入力してください。',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'messages' => $validator->errors()->all(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
