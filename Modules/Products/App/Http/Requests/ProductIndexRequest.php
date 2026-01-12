<?php

namespace Modules\Products\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\DynamicCategories\App\Models\DynamicCategorySectionItem;

class ProductIndexRequest extends FormRequest
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
            'home_page_section_id' => ['sometimes', 'nullable', 'integer', 'exists:home_page,id'],
            'dynamic_category_section_id' => ['sometimes', 'nullable', 'integer', 'exists:dynamic_category_sections,id'],
            'dynamic_category_menu_id' => ['sometimes', 'nullable', 'integer', 'exists:dynamic_category_section_items,menu_item_parent_id'],
            'dynamic_shop_section_id' => ['sometimes', 'nullable', 'integer', 'exists:dynamic_shop_sections,id'],
            'dynamic_shop_menu_id' => ['sometimes', 'nullable', 'integer', 'exists:dynamic_shop_section_items,menu_item_parent_id'],
            'is_all_menu_item' => ['sometimes', 'nullable', 'boolean'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $isAllMenuItem = false;
        if ($this->dynamic_category_menu_id) {
            $isAllMenuItem = DynamicCategorySectionItem::where('menu_item_parent_id', $this->dynamic_category_menu_id)
                ->where('is_all_menu_item', true)
                ->exists();
        }
        $this->merge([
            'is_all_menu_item' => $isAllMenuItem,
        ]);
    }

    public function messages(): array
    {
        return [
            'home_page_section_id.integer' => __('validation.integer'),
            'home_page_section_id.exists' => __('validation.exists'),
            'dynamic_category_section_id.integer' => __('validation.integer'),
            'dynamic_category_section_id.exists' => __('validation.exists'),
            'dynamic_category_menu_id.integer' => __('validation.integer'),
            'dynamic_category_menu_id.exists' => __('validation.exists'),
            'dynamic_shop_section_id.integer' => __('validation.integer'),
            'dynamic_shop_section_id.exists' => __('validation.exists'),
            'dynamic_shop_menu_id.integer' => __('validation.integer'),
            'dynamic_shop_menu_id.exists' => __('validation.exists'),
            'per_page.integer' => __('validation.integer'),
            'per_page.min' => __('validation.min.string'),
            'per_page.max' => __('validation.max.string'),
        ];
    }

    public function attributes(): array
    {
        return [
            'home_page_section_id' => __('products::messages.attributes.home_page_section_id'),
            'dynamic_category_section_id' => __('products::messages.attributes.dynamic_category_section_id'),
            'dynamic_category_menu_id' => __('products::messages.attributes.dynamic_category_menu_id'),
            'dynamic_shop_section_id' => __('products::messages.attributes.dynamic_shop_section_id'),
            'dynamic_shop_menu_id' => __('products::messages.attributes.dynamic_shop_menu_id'),
            'per_page' => __('products::messages.attributes.per_page'),
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
