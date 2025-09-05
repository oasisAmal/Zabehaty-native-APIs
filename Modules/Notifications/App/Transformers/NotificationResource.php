<?php

namespace Modules\Notifications\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'data' => $this->data['data'],
            'read_at' => ($this->read_at) ? $this->read_at->diffForHumans() : $this->read_at,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
