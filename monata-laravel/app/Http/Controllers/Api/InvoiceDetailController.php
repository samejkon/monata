<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvocieDetail\IdsRequest;
use App\Http\Requests\InvocieDetail\InvoiceDetailRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\InvoiceDetailResource;
use App\Services\InvoiceDetailService;

class InvoiceDetailController extends Controller
{
    public function __construct(
        protected InvoiceDetailService $service
    ) {}

    /**
     * Display the specified booking invoice detail.
     *
     * @param  int  $id
     * @return \App\Http\Resources\BookingResource
     * @return \App\Http\Resources\InvoiceDetailResource
     */
    public function index(int $id)
    {
        $data =  $this->service->get($id);

        return new BookingResource($data['booking'])->additional([
            'invoice_details' => InvoiceDetailResource::collection($data['invoice_details'])
        ]);
    }

    /**
     * Create or update the invoice details associated with a booking.
     *
     * This method performs a database transaction to create or update the
     * invoice details associated with a booking. It returns the result as an
     * array containing the booking instance and the collection of invoice
     * detail instances.
     *
     * @param  \App\Http\Requests\InvocieDetail\InvoiceDetailRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSave(InvoiceDetailRequest $request, $id)
    {
        $data = $this->service->upSert($request->validated(), $id);

        return new BookingResource($data['booking'])->additional([
            'invoice_details' => InvoiceDetailResource::collection($data['invoice_details'])
        ]);
    }

    /**
     * Remove the specified invoice detail from storage.
     *
     * @param  int|array  $ids
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id, int $idInvocie): \Illuminate\Http\Response
    {
        $this->service->deleteService($id, $idInvocie);

        return response()->noContent();
    }
}
