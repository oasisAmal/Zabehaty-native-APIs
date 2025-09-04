<?php

namespace Modules\Auth\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendOtpRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile' => ['required', (new Phone)->countryField('mobile_country_code'), 'exists:user,mobile'],
            'mobile_country_code' => 'required|string|max:255',
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
            'mobile.exists' => __('auth::messages.mobile_exists'),
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
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(validationErrors($validator->errors()->toArray()));
    }
}
