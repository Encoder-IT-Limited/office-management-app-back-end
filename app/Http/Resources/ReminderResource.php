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
            'remind_at' => $this->remind_at,
            'message' => $this->message,
            'status' => $this->status,
            'user' => new UserListResource($this->whenLoaded('users')),
            'project' => new ProjectListResource($this->whenLoaded('project')),
            'created_at' => $this->created_at,
        ];
    }
}
