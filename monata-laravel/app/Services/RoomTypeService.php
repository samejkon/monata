<?php

namespace App\Services;

use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RoomTypeService
{
    public function __construct(
        protected RoomType $model,
        protected Property $property
    ) {}

    /**
     * Search room types by keyword.
     *
     * @param  array  $keyword
     * @return \Illuminate\Support\Collection
     */
    public function get($keyword)
    {
        $query  = $this->model->query();
        $query->where('name', 'like', '%' . Arr::get($keyword, 'keyword') . '%');

        return $query->get();
    }

    /**
     * Create a room type.
     *
     * @param  array  $data
     * @return \App\Models\RoomType
     */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $record = $this->model->create(['name' => $data['name']]);

            $record->roomProperties()->createMany($data['properties']);

            return $record;
        });
    }

    /**
     * Update a room type.
     *
     * @param  array  $data
     * @param  int  $id
     * @return \App\Models\RoomType
     */
    public function update($data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $record = $this->model->findOrFail($id);
            $record->update(['name' => $data['name']]);

            $record->properties()->sync($data['properties']);

            return $record;
        });
    }

    /**
     * Delete a room type.
     *
     * @param  int  $id
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    //TODO: Wait data rooms
    // public function delete($id)
    // {
    //     return DB::transaction(function () use ($id) {
    //         $record = $this->model->findOrFail($id);

    //         if ($record->rooms()->exists()) {
    //             throw new \Exception("Room type has associated rooms.");
    //         }

    //         $record->roomProperties()->delete();

    //         return $record->delete();
    //     });
    // }
}
