<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateJobMetadataRequest extends FormRequest
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
            'calorie' => 'required|integer',
            'steps' => 'required|integer',
            'exercise_level' => 'required|integer',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'calorie.required' => 'カロリーは必須です。',
            'calorie.integer' => 'カロリーは整数で入力してください。',
            'steps.required' => '歩数は必須です。',
            'steps.integer' => '歩数は整数で入力してください。',
            'exercise_level.required' => '運動レベルは必須です。',
            'exercise_level.integer' => '運動レベルは整数で入力してください。',
            'tag_ids.required' => 'タグは必須です。',
            'tag_ids.array' => 'タグは配列で入力してください。',
            'tag_ids.*.exists' => 'タグが存在しません。',
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
