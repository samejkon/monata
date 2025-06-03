<script setup>
import { ref, computed, watch } from 'vue';
import moment from 'moment';
import { Printer } from 'lucide-vue-next'; // Icon cho nút in

const props = defineProps({
  show: Boolean,
  bookingData: Object, // Sẽ chứa toàn bộ thông tin booking đã checkout, bao gồm customer, invoice_details, total_payment, etc.
});

const emit = defineEmits(['close']);

const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(Number(value))) return '0 VNĐ';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  return moment(dateTimeString).format('HH:mm:ss DD/MM/YYYY');
};

const invoiceServices = computed(() => {
  // Giả sử bookingData.invoice_details chứa danh sách các dịch vụ đã lưu của hóa đơn này
  // Backend khi checkout đã tính total_payment bao gồm cả tiền phòng và tiền dịch vụ rồi.
  // Nếu invoice_details không có sẵn hoặc cần lấy lại, sẽ cần thêm logic fetch.
  // Trong BookingService->checkout, $priceService đã được tính.
  // total_payment trong booking sau khi checkout là tổng cuối cùng.
  // Chúng ta cầnแยก tiền phòng và tiền dịch vụ để hiển thị.
  // Tạm thời giả định bookingData có các trường:
  // bookingData.room_price_initial (giá phòng ban đầu/tạm tính trước dịch vụ)
  // bookingData.invoice_details (danh sách dịch vụ)
  // bookingData.total_payment (tổng tiền cuối cùng sau checkout)
  return props.bookingData?.invoice_details || [];
});

const roomPrice = computed(() => {
    // total_payment của booking object là tổng tiền cuối cùng (phòng + dịch vụ)
    // Ta cần giá phòng gốc. Nếu booking object có trường giá phòng gốc (ví dụ: room_charge, initial_room_price)
    // Hoặc nếu bookingData.total_payment là tổng cuối cùng, và ta có tổng dịch vụ.
    // total_payment (sau checkout) = room_price_actual + total_service_price
    // Để đơn giản, nếu bookingData.total_payment đã là tổng cuối cùng,
    // và ta có thể tính totalServicePrice, thì roomPriceForDisplay = bookingData.total_payment - totalServicePrice.
    // Tuy nhiên, hàm checkout của bạn cập nhật booking.total_payment = priceRoom + priceService.
    // Nên total_payment này chính là tổng cuối cùng.
    // Để hiển thị riêng tiền phòng, chúng ta cần biết giá phòng *trước khi* cộng dịch vụ.
    // Giả sử có một trường như `room_charge_at_checkout` hoặc `price_room_final` trong `bookingData`.
    // Hoặc, nếu `bookingData.total_payment` LÀ tổng cuối cùng, và `totalServiceCost` được tính từ `invoiceServices`,
    // thì `room_price = bookingData.total_payment - totalServiceCost`.
    // Cho mục đích hiển thị, tôi sẽ tạm giả định `bookingData` có một trường `price_room_final`
    // Hoặc chúng ta có thể suy luận nếu `total_payment` là tổng cuối cùng.
    // Trong BookingService, $priceRoom được lấy từ $booking->total_payment *trước khi* update.
    // Đây chính là giá phòng mà ta cần hiển thị.
    // Vậy, ta cần một trường trong bookingData đại diện cho giá phòng này.
    // Giả sử đó là `bookingData.base_room_price` hoặc `bookingData.room_amount_before_services`

    // Dựa trên hàm checkout của bạn, `total_payment` trong `Booking` được cập nhật thành `priceRoom + priceService`.
    // `priceRoom` được lấy từ `booking->total_payment` *trước khi* nó được cập nhật.
    // Vậy, nếu `bookingData` là booking *sau khi* đã checkout, `bookingData.total_payment` là tổng cuối.
    // Để lấy `priceRoom` riêng, chúng ta cần backend trả về giá trị này một cách rõ ràng,
    // hoặc trừ đi tổng giá dịch vụ từ `bookingData.total_payment`.

    // Giả sử backend trả về bookingData.room_price (là giá phòng đã được xác định tại thời điểm checkout, không bao gồm dịch vụ)
    return props.bookingData?.room_price || 0; // Cần đảm bảo trường này có trong bookingData
});

const totalServiceCost = computed(() => {
  return invoiceServices.value.reduce((sum, detail) => {
    return sum + (parseFloat(detail.price) * parseInt(detail.quantity));
  }, 0);
});

const finalTotalAmount = computed(() => {
  // total_payment từ bookingData đã là tổng cuối cùng sau khi checkout
  return props.bookingData?.total_payment || 0;
});

const closeModal = () => {
  emit('close');
};

const printInvoice = () => {
  // Logic để ẩn các nút và phần không cần thiết khi in
  const printableArea = document.getElementById('printable-invoice-area');
  if (printableArea) {
    const printContents = printableArea.innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    // Reload lại các event listener nếu cần, hoặc reload trang/component
    // Đối với Vue, tốt hơn là có một CSS riêng cho print media (@media print)
  } else {
    window.print(); // Fallback đơn giản
  }
};

watch(() => props.bookingData, (newVal) => {
    // console.log("Booking data for ViewInvoiceModal:", newVal);
    // Bạn có thể thêm log ở đây để kiểm tra dữ liệu nhận được
}, { deep: true });

</script>

<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewInvoiceModalLabel">Chi Tiết Hóa Đơn - Đặt phòng #{{ bookingData?.id }}</h5>
          <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="printable-invoice-area">
          <div v-if="!bookingData">
            <p>Không có dữ liệu hóa đơn để hiển thị.</p>
          </div>
          <div v-else>
            <!-- Thông tin khách hàng và đặt phòng -->
            <div class="customer-info mb-4">
              <h6 class="mb-3">Thông tin Khách hàng & Đặt phòng</h6>
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Khách hàng:</strong> {{ bookingData.guest_name || bookingData.customer?.name || 'N/A' }}</p>
                  <p><strong>Email:</strong> {{ bookingData.guest_email || bookingData.customer?.email || 'N/A' }}</p>
                  <p><strong>Số điện thoại:</strong> {{ bookingData.guest_phone || bookingData.customer?.phone || 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>Mã đặt phòng:</strong> #{{ bookingData.id }}</p>
                  <p><strong>Ngày nhận phòng:</strong> {{ bookingData.check_in_date ? formatDateTime(bookingData.check_in_date) : 'N/A' }}</p>
                  <p><strong>Ngày trả phòng:</strong> {{ bookingData.check_out_date ? formatDateTime(bookingData.check_out_date) : 'N/A' }}</p>
                  <!-- Thêm thông tin phòng nếu cần, ví dụ: bookingData.room.name -->
                   <p><strong>Phòng:</strong> {{ bookingData.room?.name || 'N/A' }}</p>
                </div>
              </div>
            </div>
            <hr/>

            <!-- Chi tiết thanh toán -->
            <div class="payment-details">
              <h6 class="mb-3">Chi tiết Thanh toán</h6>
              
              <!-- Tiền phòng -->
              <div class="row mb-2">
                <div class="col-8"><strong>Tiền phòng:</strong></div>

                <div class="col-4 text-end">{{ formatCurrency(roomPrice) }}</div>
              </div>
              
              <!-- Danh sách dịch vụ -->
              <div v-if="invoiceServices.length > 0">
                <p><strong>Dịch vụ đã sử dụng:</strong></p>
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Tên Dịch Vụ</th>
                      <th class="text-center">Số Lượng</th>
                      <th class="text-end">Đơn Giá</th>
                      <th class="text-end">Thành Tiền</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="service in invoiceServices" :key="service.id || service.client_temp_id || service.service_id">
                      <td>{{ service.name || service.service?.name }}</td>
                      <td class="text-center">{{ service.quantity }}</td>
                      <td class="text-end">{{ formatCurrency(service.price || service.service?.price) }}</td>
                      <td class="text-end">{{ formatCurrency((service.price || service.service?.price) * service.quantity) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3" class="text-end fw-bold">Tổng tiền dịch vụ:</td>
                      <td class="text-end fw-bold">{{ formatCurrency(totalServiceCost) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div v-else>
                <p><em>Không có dịch vụ nào được sử dụng.</em></p>
              </div>
              <hr/>
              
              <!-- Tổng cộng cuối cùng -->
              <div class="row mt-3">
                <div class="col-8 h5"><strong>TỔNG CỘNG THANH TOÁN:</strong></div>
                <div class="col-4 text-end h5">{{ formatCurrency(finalTotalAmount) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            Đóng
          </button>
          <button type="button" class="btn btn-primary" @click="printInvoice" :disabled="!bookingData">
            <Printer class="me-1" size="16"/> In Hóa Đơn
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-dialog {
  max-width: 800px; 
}
.table th, .table td {
  vertical-align: middle;
}
.customer-info p, .payment-details p {
  margin-bottom: 0.5rem;
}
/* CSS cho bản in */
@media print {
  body * {
    visibility: hidden;
  }
  #printable-invoice-area, #printable-invoice-area * {
    visibility: visible;
  }
  #printable-invoice-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
  .modal-footer button { /* Ẩn nút trong bản in nếu chúng nằm ngoài #printable-invoice-area */
    display: none !important;
  }
  /* Nếu modal-footer nằm trong #printable-invoice-area và bạn muốn ẩn nó khi in: */
  /*
  #printable-invoice-area .modal-footer {
    display: none !important;
  }
  */
}
</style> 