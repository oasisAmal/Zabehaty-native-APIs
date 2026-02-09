<?php

namespace Modules\Products\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddRemoveFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_id' => $this->product_id,
            'is_favorite' => $this->is_favorite,
        ]);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'is_favorite' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => __('validation.required'),
            'product_id.integer' => __('validation.integer'),
            'product_id.exists' => __('validation.exists'),
            'is_favorite.required' => __('validation.required'),
            'is_favorite.boolean' => __('validation.boolean'),
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => __('products::messages.attributes.product_id'),
            'is_favorite' => __('products::messages.attributes.is_favorite'),
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}
