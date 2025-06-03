<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\InvoiceDetail;
use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InvoiceDetailService
{
    public function __construct(
        protected Booking $booking,
        protected InvoiceDetail $invoiceModel
    ) {}

    /**
     * Retrieve a booking and its associated invoice details by booking ID.
     *
     * This method retrieves a booking by its ID and the associated invoice
     * details. It returns the result as an array containing the booking
     * instance and the collection of invoice detail instances.
     *
     * @param  int  $id  The ID of the booking to be retrieved.
     * @return array  The booking instance and its invoice details.
     */
    public function get($id): array
    {
        $booking = $this->checkBooking($id);

        $invoiceDetails = $this->invoiceModel->where('booking_id', $id)->get();

        return   $data = [
            'booking' => $booking,
            'invoice_details' => $invoiceDetails
        ];
    }

    /**
     * Create or update the invoice details associated with a booking.
     *
     * This method performs a database transaction to create or update the
     * invoice details associated with a booking. It returns the result as an
     * array containing the booking instance and the collection of invoice
     * detail instances.
     *
     * @param  array  $data  The invoice detail data.
     * @param  int    $id    The ID of the booking to be updated.
     * @return array  The booking instance and its invoice details.
     */
    public function upSert(array $data, int $id): array
    {
        return DB::transaction(function () use ($data, $id) {
            $booking = $this->checkBooking($id);

            $invoiceDetails = [];
            foreach ($data['invoice_details'] as $detail) {
                $service = Service::findOrFail($detail['service_id']);

                $invoiceDetailData = [
                    'id' => Arr::get($detail, 'id'),
                    'booking_id' => $id,
                    'service_id' => $detail['service_id'],
                    'name' => $service->name,
                    'price' => $service->price,
                    'quantity' => $detail['quantity'],
                ];

                $updatedDetail = $this->invoiceModel->updateOrCreate(
                    ['id' => Arr::get($detail, 'id')],
                    $invoiceDetailData
                );

                $invoiceDetails[] = $updatedDetail;
            }

            return [
                'booking' => $booking,
                'invoice_details' => $invoiceDetails
            ];
        });
    }

    /**
     * Delete specific invoice details associated with a booking.
     *
     * This method performs a database transaction to delete the invoice
     * details with the specified IDs associated with the given booking ID.
     * It returns true if any invoice details were successfully deleted,
     * false otherwise.
     *
     * @param  int  $id   The ID of the booking associated with the invoice details.
     * @param  array  $ids  The IDs of the invoice details to be deleted.
     * @return bool  True if any invoice details were deleted, false otherwise.
     */
    public function deleteService(int $id, int $idInvoice): bool
    {
        return DB::transaction(function () use ($id, $idInvoice) {
            $this->checkBooking($id);

            $deleted = $this->invoiceModel->where('id', $idInvoice)->delete();

            return $deleted;
        });
    }

    /**
     * Retrieve a booking by its ID, given that the booking status is CHECK_IN.
     *
     * This method throws an exception if the booking is not found.
     *
     * @param  int  $id  The ID of the booking to be retrieved.
     * @return \App\Models\Booking  The booking instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function checkBooking(int $id): Booking
    {
        return $this->booking->where('status', BookingStatus::CHECK_IN)
            ->where('id', $id)
            ->firstOrFail();
    }
}
