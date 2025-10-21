<?php

namespace Modules\Users\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'mobile' => $this->mobile,
            'country_code' => $this->country_code,
            'street_name' => $this->street_name,
            'apartment_num' => $this->apartment_num,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'emirate_id' => $this->emirate_id,
            'region_id' => $this->region_id,
            'branch_id' => $this->branch_id,
            'building_number' => $this->building_number,
            'notes' => $this->notes,
            'address_type' => $this->address_type,
            'is_gift' => (bool) $this->is_gift,
            'receiver_name' => $this->receiver_name,
            'show_sender_name' => (bool) $this->show_sender_name,
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}


