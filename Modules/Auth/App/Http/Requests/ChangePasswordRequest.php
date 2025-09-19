<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\DeviceTokenType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
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
            'mobile_country_code' => ['required'],
            'mobile' => ['required', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'new_password' => 'required|string|max:255',
            'confirm_password' => 'required|string|max:255|same:new_password',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        //
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
            'new_password.required' => __('auth::messages.new_password_required'),
            'confirm_password.required' => __('auth::messages.confirm_password_required'),
            'confirm_password.same' => __('auth::messages.confirm_password_same'),
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
            'new_password' => __('auth::messages.attributes.new_password'),
            'confirm_password' => __('auth::messages.attributes.confirm_password'),
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
