<?php

namespace Modules\Users\App\Http\Requests;

use App\Enums\MobileRegex;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'emirate_id' => ['sometimes', 'nullable', 'numeric', 'exists:emirates,id'],
            'region_id' => ['sometimes', 'nullable', 'numeric', 'exists:regions,id'],
            'main_type' => ['sometimes', 'nullable', 'string', Rule::in('home', 'office', 'others')],
            'address_type' => ['sometimes', 'nullable', 'string'],
            'directions' => ['sometimes', 'nullable', 'string'],
            'address' => ['required', 'string'],
            'street_name' => ['sometimes', 'nullable', 'string'],
            'apartment_num' => ['sometimes', 'nullable', 'string'],
            'name' => ['sometimes', 'nullable', 'string'],
            'mobile' => ['sometimes', 'nullable', 'regex:' . getMobileRegexBasedOnCountryCode($this->mobile_country_code)],
            'mobile_country_code' => ['sometimes', 'nullable', 'string', Rule::in(MobileRegex::getKeys())],
            'validate_mobile' => ['sometimes', 'nullable'],
            'country_code' => ['sometimes', 'nullable', 'string'],
            'street_name' => ['sometimes', 'nullable', 'string'],
            'apartment_num' => ['sometimes', 'nullable', 'string'],
            'building_number' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'is_gift' => ['sometimes', 'nullable', 'boolean'],
            'receiver_name' => ['sometimes', 'nullable', 'string'],
            'show_sender_name' => ['sometimes', 'nullable', 'boolean'],
            'is_default' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => $this->mobile ? format_mobile_number($this->mobile) : null,
            'validate_mobile' => $this->mobile ? format_mobile_number_to_database($this->mobile, $this->mobile_country_code) : null,
            'country_code' => $this->mobile ? $this->mobile_country_code : null,
            'main_type' => $this->main_type ?? 'others',
            'address_type' => $this->address_type ?? 'others',
        ]);
    }

    public function messages(): array
    {
        return [
            'lat.required' => __('users::messages.lat_required'),
            'lat.numeric' => __('users::messages.lat_numeric'),
            'lng.required' => __('users::messages.lng_required'),
            'lng.numeric' => __('users::messages.lng_numeric'),
            'mobile.required' => __('users::messages.mobile_required'),
            'mobile.regex' => __('users::messages.mobile_regex'),
            'mobile_country_code.required' => __('users::messages.mobile_country_code_required'),
            'mobile_country_code.string' => __('users::messages.mobile_country_code_string'),
            'mobile_country_code.in' => __('users::messages.mobile_country_code_in'),
            'emirate_id.required' => __('users::messages.emirate_id_required'),
            'emirate_id.numeric' => __('users::messages.emirate_id_numeric'),
            'emirate_id.exists' => __('users::messages.emirate_id_exists'),
            'region_id.required' => __('users::messages.region_id_required'),
            'region_id.numeric' => __('users::messages.region_id_numeric'),
            'region_id.exists' => __('users::messages.region_id_exists'),
            'address.required' => __('users::messages.address_required'),
            'address.string' => __('users::messages.address_string'),
            'street_name.string' => __('users::messages.street_name_string'),
            'apartment_num.string' => __('users::messages.apartment_num_string'),
            'name.string' => __('users::messages.name_string'),
            'mobile.string' => __('users::messages.mobile_string'),
            'country_code.string' => __('users::messages.country_code_string'),
            'building_number.string' => __('users::messages.building_number_string'),
            'notes.string' => __('users::messages.notes_string'),
            'directions.string' => __('users::messages.directions_string'),
            'directions.max' => __('users::messages.directions_max'),
            'is_gift.boolean' => __('users::messages.is_gift_boolean'),
            'receiver_name.string' => __('users::messages.receiver_name_string'),
            'show_sender_name.boolean' => __('users::messages.show_sender_name_boolean'),
            'is_default.boolean' => __('users::messages.is_default_boolean'),
            'is_active.boolean' => __('users::messages.is_active_boolean'),
        ];
    }

    public function attributes(): array
    {
        return [
            'lat' => __('users::messages.attributes.lat'),
            'lng' => __('users::messages.attributes.lng'),
            'emirate_id' => __('users::messages.attributes.emirate_id'),
            'region_id' => __('users::messages.attributes.region_id'),
            'main_type' => __('users::messages.attributes.main_type'),
            'address_type' => __('users::messages.attributes.address_type'),
            'directions' => __('users::messages.attributes.directions'),
            'address' => __('users::messages.attributes.address'),
            'street_name' => __('users::messages.attributes.street_name'),
            'apartment_num' => __('users::messages.attributes.apartment_num'),
            'name' => __('users::messages.attributes.name'),
            'mobile' => __('users::messages.attributes.mobile'),
            'country_code' => __('users::messages.attributes.country_code'),
            'building_number' => __('users::messages.attributes.building_number'),
            'notes' => __('users::messages.attributes.notes'),
            'is_gift' => __('users::messages.attributes.is_gift'),
            'receiver_name' => __('users::messages.attributes.receiver_name'),
            'show_sender_name' => __('users::messages.attributes.show_sender_name'),
            'is_default' => __('users::messages.attributes.is_default'),
            'is_active' => __('users::messages.attributes.is_active'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(validationErrors($validator->errors()->all()));
    }
}
