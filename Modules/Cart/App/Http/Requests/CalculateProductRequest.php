<?php

namespace Modules\Cart\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CalculateProductRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size_id' => 'nullable|integer|exists:sub_products,id',
            'addon_items' => 'nullable|array',
            'addon_items.*' => 'required|integer',
        ];
    }

     /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product_id.required' => __('validation.required'),
            'product_id.integer' => __('validation.integer'),
            'product_id.exists' => __('validation.exists'),
            'quantity.required' => __('validation.required'),
            'quantity.integer' => __('validation.integer'),
            'quantity.min' => __('validation.min.string'),
            'size_id.integer' => __('validation.integer'),
            'size_id.exists' => __('validation.exists'),
            'addon_items.array' => __('validation.array'),
            'addon_items.*.required' => __('validation.required'),
            'addon_items.*.integer' => __('validation.integer'),
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'product_id' => __('cart::messages.attributes.product_id'),
            'quantity' => __('cart::messages.attributes.quantity'),
            'size_id' => __('cart::messages.attributes.size_id'),
            'addon_items' => __('cart::messages.attributes.addon_items'),
            'addon_items.*' => __('cart::messages.attributes.addon_items.*'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}

