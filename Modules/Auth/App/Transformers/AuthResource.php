<?php

namespace Modules\Auth\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'is_guest' => $this->is_guest,
            'is_verified' => $this->is_verified,
            'rating' => $this->rating,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
        ];
    }
}
