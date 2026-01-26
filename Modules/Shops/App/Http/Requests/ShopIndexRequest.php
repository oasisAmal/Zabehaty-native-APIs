<?php

namespace Modules\Shops\App\Http\Requests;

use App\Traits\CountryQueryBuilderTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShopIndexRequest extends FormRequest
{
    use CountryQueryBuilderTrait;

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
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'dynamic_category_section_id' => ['sometimes', 'nullable', 'integer', 'exists:dynamic_category_sections,id'],
            'dynamic_category_menu_id' => ['sometimes', 'nullable', 'integer', 'exists:dynamic_category_section_items,menu_item_parent_id'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $categoryId = null;
        $dynamicCategorySectionId = null;
        if ($this->dynamic_category_menu_id) {
            $dynamicCategorySectionItem = $this->getCountryConnection()
                ->table('dynamic_category_section_items')
                ->join('dynamic_category_sections', 'dynamic_category_sections.id', '=', 'dynamic_category_section_items.dynamic_category_section_id')
                ->select('dynamic_category_section_items.dynamic_category_section_id', 'dynamic_category_sections.category_id')
                ->where('menu_item_parent_id', $this->dynamic_category_menu_id)
                ->first();
            if ($dynamicCategorySectionItem) {
                $dynamicCategorySectionId = $dynamicCategorySectionItem->dynamic_category_section_id;
                $categoryId = $dynamicCategorySectionItem->category_id;
            }
        }
        $this->merge([
            'category_id' => $categoryId,
            'dynamic_category_section_id' => $dynamicCategorySectionId,
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
            'category_id.integer' => __('validation.integer'),
            'category_id.exists' => __('validation.exists'),
            'per_page.integer' => __('validation.integer'),
            'per_page.min' => __('validation.min.string'),
            'per_page.max' => __('validation.max.string'),
        ];
    }

    public function attributes(): array
    {
        return [
            'home_page_section_id' => __('shops::messages.attributes.home_page_section_id'),
            'category_id' => __('shops::messages.attributes.category_id'),
            'dynamic_category_section_id' => __('shops::messages.attributes.dynamic_category_section_id'),
            'dynamic_category_menu_id' => __('shops::messages.attributes.dynamic_category_menu_id'),
            'category_id' => __('shops::messages.attributes.category_id'),
            'per_page' => __('shops::messages.attributes.per_page'),
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
