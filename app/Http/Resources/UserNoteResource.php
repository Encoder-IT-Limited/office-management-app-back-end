<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserNoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
