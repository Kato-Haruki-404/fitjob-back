<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'firstNameKana' => ['nullable', 'string', 'max:255'],
            'lastNameKana' => ['nullable', 'string', 'max:255'],
            'birthDate' => ['nullable', 'date'],
            'age' => ['nullable', 'integer'],
            'gender' => ['nullable', 'in:male,female,other'],
            'postalCode' => ['nullable', 'string', 'max:8'],
            'prefecture' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'tel' => ['nullable', 'string', 'max:15'],
            'identityDocument' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            'biography' => ['nullable', 'string', 'max:1000'],
            'resumeFile' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.max' => '名を入力してください。',
            'lastName.max' => '姓を入力してください。',
            'firstNameKana.max' => '名（カナ）を入力してください。',
            'lastNameKana.max' => '姓（カナ）を入力してください。',
            'birthDate.date' => '生年月日を入力してください。',
            'age.integer' => '年齢を入力してください。',
            'gender.in' => '性別を選択してください。',
            'postalCode.max' => '郵便番号を入力してください。',
            'prefecture.max' => '都道府県を選択してください。',
            'address.max' => '住所を入力してください。',
            'tel.max' => '電話番号を入力してください。',
            'identityDocument.mimes' => '本人確認書類を選択してください。',
            'biography.max' => '自己紹介を入力してください。',
            'resumeFile.mimes' => '履歴書を選択してください。',
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
