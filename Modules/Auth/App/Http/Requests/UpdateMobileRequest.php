<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\MobileRegex;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMobileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile' => ['required', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'mobile_country_code' => ['required', 'string', Rule::in(MobileRegex::getKeys())],
            'validate_mobile' => ['sometimes', 'nullable', 'unique:user,mobile,' . auth('api')->id()],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => format_mobile_number($this->mobile),
            'validate_mobile' => format_mobile_number_to_database($this->mobile, $this->mobile_country_code),
        ]);
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'mobile.required' => __('auth::messages.mobile_required'),
            'mobile_country_code.required' => __('auth::messages.mobile_country_code_required'),
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
            'mobile' => __('auth::messages.attributes.mobile'),
            'mobile_country_code' => __('auth::messages.attributes.mobile_country_code'),
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
