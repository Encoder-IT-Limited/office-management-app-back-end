<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'reference' => $this->reference,
            'project_id' => $this->project_id,
            'assignee_id' => $this->assignee_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'site' => $this->site,
            'estimated_time' => $this->estimated_time,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assignee' => new UserListResource($this->whenLoaded('assignee')),
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
