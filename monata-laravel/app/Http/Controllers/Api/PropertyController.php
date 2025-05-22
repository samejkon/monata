<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Properties\CreatePropertyRequest;
use App\Http\Requests\Properties\SeachPropertyRequest;
use App\Http\Requests\Properties\UpdatePropertyRequest;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        protected PropertyService $service
    ) {}

    /**
     * Search for properties.
     *
     * @param \App\Http\Requests\Properties\SeachPropertyRequest $request
     * @return mixed
     */
    public function index(SeachPropertyRequest $request)
    {
        return $this->service->get($request->validated());
    }

    /**
     * Create a new property.
     *
     * @param \App\Http\Requests\Properties\CreatePropertyRequest $request
     * @return mixed
     */
    public function store(CreatePropertyRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * Update an existing property.
     *
     * @param \App\Http\Requests\Properties\UpdatePropertyRequest $request
     * @return mixed
     */
    public function updateMultiple(UpdatePropertyRequest $request)
    {
        $this->service->update($request->validated());
        return true;
    }
}
