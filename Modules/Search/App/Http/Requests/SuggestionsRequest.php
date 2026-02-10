<?php

namespace Modules\Search\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SuggestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => __('validation.required'),
            'q.string' => __('validation.string'),
            'q.min' => __('validation.min.string'),
            'q.max' => __('validation.max.string'),
            'limit.integer' => __('validation.integer'),
            'limit.min' => __('validation.min.numeric'),
            'limit.max' => __('validation.max.numeric'),
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => __('search::messages.attributes.query'),
            'limit' => __('search::messages.attributes.limit'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}
