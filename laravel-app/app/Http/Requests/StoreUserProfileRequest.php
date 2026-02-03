<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserProfileRequest extends FormRequest
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
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'firstNameKana' => ['required', 'string', 'max:255'],
            'lastNameKana' => ['required', 'string', 'max:255'],
            'birthDate' => ['required', 'date'],
            'age' => ['required', 'integer'],
            'gender' => ['required', 'in:male,female,other'],
            'postalCode' => ['required', 'string', 'max:8'],
            'prefecture' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'tel' => ['required', 'string', 'max:15'],
            'identityDocument' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            'biography' => ['required', 'string', 'max:1000'],
            'resumeFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.required' => '名を入力してください。',
            'lastName.required' => '姓を入力してください。',
            'firstNameKana.required' => '名（カナ）を入力してください。',
            'lastNameKana.required' => '姓（カナ）を入力してください。',
            'birthDate.required' => '生年月日を入力してください。',
            'age.required' => '年齢を入力してください。',
            'gender.required' => '性別を選択してください。',
            'postalCode.required' => '郵便番号を入力してください。',
            'prefectureId.required' => '都道府県を選択してください。',
            'address.required' => '住所を入力してください。',
            'tel.required' => '電話番号を入力してください。',
            'identityDocument.required' => '本人確認書類を選択してください。',
            'biography.required' => '自己紹介を入力してください。',
            'resumeFile.required' => '履歴書を選択してください。',
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
