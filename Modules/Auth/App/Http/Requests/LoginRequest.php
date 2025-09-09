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
            'mobile' => 'required|string|max:255',
            'mobile_country_code' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'device_token' => ['required'],
            'device_type' => ['required', Rule::in(DeviceTokenType::ANDROID, DeviceTokenType::IOS)],
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
            'mobile.required' => __('auth::messages.mobile_required'),
            'mobile_country_code.required' => __('auth::messages.mobile_country_code_required'),
            'password.required' => __('auth::messages.password_required'),
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
            'mobile' => __('auth::attributes.mobile'),
            'mobile_country_code' => __('auth::attributes.mobile_country_code'),
            'password' => __('auth::attributes.password'),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //
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
