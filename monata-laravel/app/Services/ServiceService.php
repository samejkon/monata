<?php

namespace App\Services;

use App\Models\Service;

class ServiceService
{

    public function __construct(
        protected Service $model,
    ) {}

    /**
     * Get services.
     *
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(array $data): \Illuminate\Database\Eloquent\Collection
    {
        $query  = $this->model->query();

        $query->when($data['name'] ?? null, function ($q, $name) {
            $q->where('name', 'like', "%$name%");
        })->when($data['price'] ?? null, function ($q, $price) {
            $q->where('price', 'like', "%$price%");
        })->when(isset($data['status']) && $data['status'] !== '', function ($q) use ($data) {
            $q->where('status', '=', $data['status']);
        });

        $services = $query->get();

        return $services;
    }

    /**
     * Create a service.
     *
     * @param  array  $data
     * @return \App\Models\Service
     */
    public function create(array $data): Service
    {
        $record = $this->model->create($data);

        return $record;
    }

    /**
     * Update a service.
     *
     * @param  array  $data
     * @param  int  $id
     * @return \App\Models\Service
     */
    public function update(array $data, $id): Service
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record;
    }

    /**
     * Delete a service.
     *
     * @param  int  $id
     * @return \App\Models\Service
     * @throws \Exception
     */
    public function delete($id): ?Service
    {
        $record = $this->model->findOrFail($id);

        $record->delete();

        return $record;
    }

    /**
     * Restore a service.
     *
     * @param  int  $id
     * @return \App\Models\Service
     */
    public function restore($id): ?Service
    {
        $service = $this->model->withTrashed()->find($id);

        if (!$service) {
            return null;
        }
        $service->restore();

        return $service;
    }
}
