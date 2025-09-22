<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\DeviceTokenType;
use App\Enums\SocialProvider;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SocialLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'social_profile_id' => 'nullable|string',
            'social_type' => ['required', Rule::in(SocialProvider::getValues())],
            'social_token' => 'required|string',
            'email' => 'nullable|email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
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
            'social_type.required' => __('auth::messages.social_type_required'),
            'social_type.in' => __('auth::messages.social_type_invalid'),
            'social_token.required' => __('auth::messages.social_token_required'),
            'social_profile_id.required' => __('auth::messages.social_profile_id_required'),
            'email.email' => __('auth::messages.email_invalid'),
            'first_name.string' => __('auth::messages.first_name_string'),
            'first_name.max' => __('auth::messages.first_name_max'),
            'last_name.string' => __('auth::messages.last_name_string'),
            'last_name.max' => __('auth::messages.last_name_max'),
            'device_token.required' => __('auth::messages.device_token_required'),
            'device_type.required' => __('auth::messages.device_type_required'),
            'device_type.in' => __('auth::messages.device_type_invalid'),
            'device_brand.string' => __('auth::messages.device_brand_string'),
            'device_brand.max' => __('auth::messages.device_brand_max'),
            'app_version.string' => __('auth::messages.app_version_string'),
            'app_version.max' => __('auth::messages.app_version_max'),
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
            'social_type' => __('auth::messages.attributes.social_type'),
            'social_token' => __('auth::messages.attributes.social_token'),
            'social_profile_id' => __('auth::messages.attributes.social_profile_id'),
            'email' => __('auth::messages.attributes.email'),
            'first_name' => __('auth::messages.attributes.first_name'),
            'last_name' => __('auth::messages.attributes.last_name'),
            'device_token' => __('auth::messages.attributes.device_token'),
            'device_type' => __('auth::messages.attributes.device_type'),
            'device_brand' => __('auth::messages.attributes.device_brand'),
            'app_version' => __('auth::messages.attributes.app_version'),
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
