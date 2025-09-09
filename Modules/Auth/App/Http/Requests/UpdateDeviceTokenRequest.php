<?php

namespace Modules\Customers\App\Http\Requests\Api;

use App\Enums\DeviceTokenType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDeviceTokenRequest extends FormRequest
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
            'device_token' => ['required', 'string'],
            'device_type' => ['required', Rule::in(DeviceTokenType::getValues())],
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
