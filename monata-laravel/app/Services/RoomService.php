<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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

    public function findById(int $id): Room
    {
        return $this->model->findOrFail($id);
    }
    
    public function insert(array $data): Room
    {
        $thumbnailPath = $this->fileService->store($data['thumbnail'], 'room/thumbnails');

        $imagePaths = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $imagePaths[] = $this->fileService->store($image, 'room/images');
            }
        }

        return DB::transaction(function () use ($data, $thumbnailPath, $imagePaths) {
            $room = new Room;
            $room->name = $data['name'];
            $room->room_type_id = $data['room_type_id'];
            $room->price = $data['price'];
            $room->description = $data['description'];
            $room->status = $data['status'];
            $room->thumbnail_path = $thumbnailPath;
            $room->save();

            foreach ($imagePaths as $imagePath) {
                $room->images()->create(['image_path' => $imagePath]);
            }

            return $room;
        });
    }

    public function update(array $data, Room $room): Room
    {
        if (isset($data['thumbnail'])) {
            $thumbnailPath = $this->fileService->store($data['thumbnail'], 'room/thumbnails');
            $room->thumbnail_path = $thumbnailPath;
        }

        $newImagePaths = [];
        if (!empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $newImagePaths[] = $this->fileService->store($image, 'room/images');
            }
        }

        return DB::transaction(function () use ($room, $data, $newImagePaths) {
            $room->name = $data['name'];
            $room->room_type_id = $data['room_type_id'];
            $room->price = $data['price'];
            $room->description = $data['description'];
            $room->status = $data['status'];
            $room->save();

            if (!empty($data['images_to_remove']) && is_array($data['images_to_remove'])) {
                $imagesToDelete = $room->images()->whereIn('id', $data['images_to_remove'])->get();

                foreach ($imagesToDelete as $image) {
                    $this->fileService->delete($image->image_path);
                    $image->delete();
                }
            }

            foreach ($newImagePaths as $imagePath) {
                $room->images()->create(['image_path' => $imagePath]);
            }

            return $room;
        });
    }

    public function delete(int $id): null
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return null;
    }

    public function restore(int $id): Room
    {
        $room = Room::withTrashed()->findOrFail($id);
        $room->restore();

        return $room;
    }
}
