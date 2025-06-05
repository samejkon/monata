<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoomClientService;
use App\Http\Resources\RoomDetailClientResource;

class RoomClientController extends Controller
{
    public function __construct(
        protected RoomClientService $service,
    ) {}

    public function index(Request $request)
    {
        //
    }

    public function show($id)
    {
        $room = $this->service->find($id);
        return new RoomDetailClientResource($room);
    }
}
