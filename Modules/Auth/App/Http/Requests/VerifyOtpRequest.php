<?php

namespace Modules\Auth\App\Http\Requests;

use App\Enums\MobileRegex;
use App\Enums\DeviceTokenType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOtpRequest extends FormRequest
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
            'mobile_country_code' => ['required', 'string', Rule::in(MobileRegex::getKeys())],
            'mobile' => ['required', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'verification_code' => 'required|numeric',
            'device_token' => 'nullable',
            'device_type' => ['nullable', Rule::in(DeviceTokenType::ANDROID, DeviceTokenType::IOS)],
            'device_brand' => 'nullable|string|max:255',
            'return_token' => 'sometimes|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => format_mobile_number($this->mobile),
        ]);
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
