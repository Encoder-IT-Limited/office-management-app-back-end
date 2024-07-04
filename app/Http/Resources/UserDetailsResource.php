<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'uploads' => $this->uploads,
            'role' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'skills' => $this->whenLoaded('skills', function () {
                $skills = $this->skills;

                return $skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'experience' => $skill->pivot->experience,
                    ];
                });
            }),
            'projects' => $this->whenLoaded('projects', function () {
                return $this->projects;
            }),
            'leaves' => $this->whenLoaded('leaves', function () {
                return $this->leaves;
            }),
            'notes' => $this->whenLoaded('notes', function () {
                return $this->notes;
            }),
            'parents' => UserListResource::collection($this->whenLoaded('parents')),
            'children' => UserListResource::collection($this->whenLoaded('children')),
        ];
    }
}
