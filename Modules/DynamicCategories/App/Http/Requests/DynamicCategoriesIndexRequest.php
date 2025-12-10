<?php

namespace Modules\DynamicCategories\App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DynamicCategoriesIndexRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => __('dynamiccategories::messages.category_id_required'),
            'category_id.integer' => __('validation.integer'),
            'category_id.exists' => __('validation.exists'),
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'category_id' => __('dynamiccategories::messages.attributes.category_id'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}

