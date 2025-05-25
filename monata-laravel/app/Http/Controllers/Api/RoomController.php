<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Room\SearchRoomRequest;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Services\RoomService;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index(SearchRoomRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $rooms = $this->roomService->get($request->validated());

        return RoomResource::collection($rooms);
    }

    /**
     * Show the form for store a new resource.
     */
    public function store(StoreRoomRequest $request): RoomResource
    {
        $room = $this->roomService->insert($request->validated());

        return new RoomResource($room);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
