<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\Common;
use App\Enums\MobileRegex;
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
            'email' => 'nullable|email|unique:user,email',
            'mobile' => ['required', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'validate_mobile' => ['sometimes', 'nullable', 'unique:user,mobile'],
            'mobile_country_code' => ['required', 'string', Rule::in(MobileRegex::getKeys())],
            'password' => 'required|string|max:255|regex:' . Common::PASSWORD_REGEX,
            // 'confirm_password' => 'required|string|max:255|same:password',
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
            'validate_mobile' => format_mobile_number_to_database($this->mobile, $this->mobile_country_code),
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
            'confirm_password.required' => __('auth::messages.confirm_password_required'),
            'confirm_password.same' => __('auth::messages.confirm_password_same'),
            'validate_mobile.unique' => __('auth::messages.validate_mobile_unique'),
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
            'first_name' => __('auth::messages.attributes.first_name'),
            'last_name' => __('auth::messages.attributes.last_name'),
            'email' => __('auth::messages.attributes.email'),
            'mobile' => __('auth::messages.attributes.mobile'),
            'mobile_country_code' => __('auth::messages.attributes.mobile_country_code'),
            'password' => __('auth::messages.attributes.password'),
            'device_brand' => __('auth::messages.attributes.device_brand'),
            'confirm_password' => __('auth::messages.attributes.confirm_password'),
            'validate_mobile' => __('auth::messages.attributes.validate_mobile'),
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
