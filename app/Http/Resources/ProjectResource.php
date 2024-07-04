<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'budget' => $this->budget,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'message' => $this->message,


            'client_id' => $this->client_id,
            'status_id' => $this->status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'notes' => ProjectNoteResource::collection($this->whenLoaded('notes')),
            'client' => new UserListResource($this->whenLoaded('client')),
            'status' => new StatusResource($this->whenLoaded('status')),
            'users' => UserListResource::collection($this->whenLoaded('users')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'teams' => $this->whenLoaded('teams'),
        ];
    }
}
