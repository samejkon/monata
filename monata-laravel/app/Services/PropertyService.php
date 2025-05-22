<?php

namespace App\Services;

use App\Models\Property;

class PropertyService
{

    public function __construct(
        protected Property $model
    ) {}

    /**
     * Retrieve a list of properties based on a search query.
     *
     * @param  array  $search
     * @return mixed
     */
    public function get(array $data): mixed
    {
        $keyword = $data['keyword'];

        $query = $this->model->query();
        $query->where('name', 'like', '%' . $keyword . '%');
        $query->orderBy('id', 'desc');

        return $query->get();
    }

    /**
     * Create a new property with the given data.
     *
     * @param  array  $data
     * @return \App\Models\Property
     */
    public function store(array $data)
    {
        $data = $this->model->create($data);
        return $data;
    }

    public function update($data)
    {
        return $this->model->query()->upsertWithAudit($data, ['id']);
    }
}
