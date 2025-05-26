<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\SearchServiceRequest;
use App\Http\Requests\Service\CreateServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Services\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceService $service,
    ) {
        $this->service = $service;
    }

    /**
     * Search services.
     *
     * @param  \App\Http\Requests\Service\SearchServiceRequest  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(SearchServiceRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $data = $request->validated();

        $services = $this->service->get($data);

        return ServiceResource::collection($services);
    }

    /**
     * Create a service.
     *
     * @param  \App\Http\Requests\Service\CreateServiceRequest  $request
     * @return \App\Http\Resources\ServiceResource
     */
    public function store(CreateServiceRequest $request): ServiceResource
    {
        $data = $request->validated();

        $service = $this->service->create($data);

        return new ServiceResource($service);
    }

    /**
     * Update a service.
     *
     * @param  \App\Http\Requests\Service\UpdateServiceRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\ServiceResource
     */
    public function update(UpdateServiceRequest $request, int $id): ServiceResource
    {
        $data = $this->service->update($request->validated(), $id);

        return new ServiceResource($data);
    }

    /**
     * Delete a service.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }
}
