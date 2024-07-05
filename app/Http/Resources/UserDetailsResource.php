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
            'roles' => $this->whenLoaded('roles', function () {
                // return roles and permissions
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'slug' => $permission->slug,
                            ];
                        }),
                    ];
                });
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
            'break_times' => $this->whenLoaded('breakTimes', function () {
                return $this->breakTimes;
            }),
            'today_attendance' => $this->whenLoaded('todayAttendance', function () {
                return $this->todayAttendance;
            }),
            'delays_count' => $this->delays_count,
            'parents' => UserListResource::collection($this->whenLoaded('parents')),
            'children' => UserListResource::collection($this->whenLoaded('children')),
        ];
    }
}
