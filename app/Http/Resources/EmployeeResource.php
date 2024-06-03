<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'designation' => $this->designation,
            'status' => $this->status,
            'delay_time' => $this->delay_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
//            'attendance' => AttendanceResource::collection($this->whenLoaded('attendance')),
//            'break_time' => BreaktimeResource::collection($this->whenLoaded('breakTimes')),
        ];
    }
}
