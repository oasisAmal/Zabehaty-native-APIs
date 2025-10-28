<?php

namespace Modules\Users\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SetDefaultAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'numeric', 'exists:user_address,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => __('users::messages.address_id_required'),
            'address_id.numeric' => __('users::messages.address_id_numeric'),
            'address_id.exists' => __('users::messages.address_id_exists'),
        ];
    }

    public function attributes(): array
    {
        return [
            'address_id' => __('users::messages.address_id'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}
