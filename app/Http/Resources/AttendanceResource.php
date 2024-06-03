<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'delay_time' => $this->delay_time,
            'date' => $this->date,
            'status' => $this->status,
            'duration' => $this->duration,
            'is_delay' => $this->isDelay,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'employee' => EmployeeResource::collection($this->whenLoaded('employee')),
            'break_time' => $this->breakTimes,

            'break_status' => $this?->employee?->breakTimes()->whereDate('start_time', Carbon::today())->whereNull('end_time')->exists(),
        ];
    }
}
