<?php

namespace Modules\Auth\App\Transformers;

use App\Enums\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\App\Http\Resources\UserAddressResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'is_guest' => (bool) $this->is_guest,
            'is_verified' => (bool) $this->is_verified,
            'rating' => (float) $this->rating ?? 0,
            'image_url' => $this->image_url,
            'default_address' => UserAddressResource::make($this->addresses()->with('emirate', 'region', 'branch')->default()->active()->first()),
            'created_at' => $this->created_at->format(Common::DATE_FORMAT_24),
            'updated_at' => $this->updated_at->format(Common::DATE_FORMAT_24),
        ];
    }
}
