<?php

namespace App\Services;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\Utils\FileService;
use App\Models\Room;

class RoomService
{
    public function __construct(
        protected Room $model,
        protected FileService $fileService,
    ) {}

    public function get(array $search): LengthAwarePaginator
    {
        $query  = $this->model->query();

        $query
            ->when(isset($search['name']), function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search['name'] . '%');
            })
            ->when(isset($search['room_type_id']), function ($q) use ($search) {
                $q->where('room_type_id', 'like', '%' . $search['room_type_id'] . '%');
            })
            ->when(isset($search['price_from']), function ($q) use ($search) {
                $q->where('price', '>=', $search['price_from']);
            })
            ->when(isset($search['price_to']), function ($q) use ($search) {
                $q->where('price', '<=', $search['price_to']);
            })
            ->when(isset($search['status']), function ($q) use ($search) {
                $q->where('status', $search['status']);
            });

        $perPage = $search['per_page'] ?? 10;
        $rooms = $query->paginate($perPage);

        return $rooms;
    }

    public function findById(int $room_id): Room
    {
        return $this->model->finOrFail($room_id);
    }
    
    public function insert(array $data): Room
    {
        $room = new Room;
        $room->name = $data['name'];
        $room->room_type_id = $data['room_type_id'];
        $room->price = $data['price'];
        $room->description = $data['description'];
        $room->status = $data['status'];

        if (isset($data['thumbnail'])) {
            $room->thumbnail_path = $this->fileService->store($data['thumbnail'], 'images/thumbnails');
        }

        $room->save();

        return $room;
    }
}