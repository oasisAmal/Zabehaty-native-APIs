<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\DeviceTokenType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'mobile' => ['required', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'mobile_country_code' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'device_token' => ['required'],
            'device_type' => ['required', Rule::in(DeviceTokenType::ANDROID, DeviceTokenType::IOS)],
            'device_brand' => 'nullable|string|max:255',
            'app_version' => 'required|string|max:255',
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
            'app_version' => $this->app_version,
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
            'first_name.required' => __('auth::messages.first_name_required'),
            'first_name.string' => __('auth::messages.first_name_string'),
            'first_name.max' => __('auth::messages.first_name_max'),
            'last_name.required' => __('auth::messages.last_name_required'),
            'last_name.string' => __('auth::messages.last_name_string'),
            'last_name.max' => __('auth::messages.last_name_max'),
            'email.required' => __('auth::messages.email_required'),
            'email.email' => __('auth::messages.email_email'),
            'email.unique' => __('auth::messages.email_unique'),
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
            'first_name' => __('auth::attributes.first_name'),
            'last_name' => __('auth::attributes.last_name'),
            'email' => __('auth::attributes.email'),
            'mobile' => __('auth::attributes.mobile'),
            'mobile_country_code' => __('auth::attributes.mobile_country_code'),
            'password' => __('auth::attributes.password'),
            'device_brand' => __('auth::attributes.device_brand'),
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
