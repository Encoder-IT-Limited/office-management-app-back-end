<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'user' => $this->whenLoaded('users'),
            'project' => $this->whenLoaded('projects'),
            'remind_at' => $this->remind_at,
            'created_at' => $this->created_at,
            'user_id' => $this->user_id,
        ];
    }
}
