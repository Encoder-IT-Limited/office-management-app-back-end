<?php

namespace App\Http\Resources;

use App\Traits\MetaResponseTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AttendanceCollection extends ResourceCollection
{
    use MetaResponseTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($data) {
                return AttendanceResource::make($data);
            }),
            ...$this->generateMeta(),
        ];
    }
}
