<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\ImageResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'room_type_id' => $this->room_type_id,
            'room_type' => $this->roomType->name,
            'price' => $this->roomType->price,
            'tumbnail_path' => $this->thumbnail_path,
            'description' => $this->description,
            'status' => $this->status,
            'images' => ImageResource::collection($this->images),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'deleted_at' => Carbon::parse($this->deleted_at)->format('Y-m-d H:i:s'),
        ];
    }
}
