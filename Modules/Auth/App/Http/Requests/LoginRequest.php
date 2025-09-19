<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\DeviceTokenType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile' => ['required', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'mobile_country_code' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'device_token' => ['required'],
            'device_type' => ['required', Rule::in(DeviceTokenType::ANDROID, DeviceTokenType::IOS)],
            'device_brand' => 'nullable|string|max:255',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'mobile' => format_mobile_number($this->mobile),
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
            'password.required' => __('auth::messages.password_required'),
            'device_brand.required' => __('auth::messages.device_brand_required'),
            'device_brand.string' => __('auth::messages.device_brand_string'),
            'device_brand.max' => __('auth::messages.device_brand_max'),
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
            'password' => __('auth::messages.attributes.password'),
            'device_brand' => __('auth::messages.attributes.device_brand'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}
