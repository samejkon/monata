<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { api } from '../../lib/axios';
import { useToast } from 'vue-toastification';
import moment from 'moment'; // Import moment
import vSelect from 'vue-select'; // Import vue-select
import 'vue-select/dist/vue-select.css'; // Import vue-select CSS

const props = defineProps({
  show: Boolean,
  booking: Object, // Sẽ chứa thông tin booking, đặc biệt là bookingId
});

const emit = defineEmits(['close', 'invoice-updated']);

const toast = useToast();
const apiUrl = import.meta.env.VITE_API_URL;

const invoiceDetails = ref([]);
const availableServices = ref([]);
const isLoading = ref(false);
const selectedServiceId = ref(null); // Dùng cho việc thêm dịch vụ mới

// Helper function to format date and time
const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  return moment(dateTimeString).format('HH:mm:ss DD/MM/YYYY');
};

// Lấy danh sách tất cả dịch vụ có sẵn
const fetchAvailableServices = async () => {
  try {
    const response = await api.get(`/services`); // Giả sử endpoint là /services
    availableServices.value = response.data?.data || [];
  } catch (error) {
    console.error('Error fetching available services:', error);
    toast.error('Không thể tải danh sách dịch vụ.');
  }
};

// Lấy chi tiết hóa đơn hiện tại của booking
const fetchInvoiceDetails = async (bookingId) => {
  if (!bookingId) return;
  isLoading.value = true;
  try {
    // Backend: InvoiceDetailService->get($id)
    const response = await api.get(`/bookings/${bookingId}/invoice-details`);
    invoiceDetails.value = response.data?.invoice_details || [];
  } catch (error) {
    console.error('Error fetching invoice details:', error);
    invoiceDetails.value = []; // Reset nếu có lỗi
    // toast.error('Không thể tải chi tiết hóa đơn.'); // Có thể không cần toast ở đây nếu booking mới chưa có hóa đơn
  } finally {
    isLoading.value = false;
  }
};

watch(() => props.booking, (newBooking) => {
  if (newBooking && newBooking.id && props.show) {
    fetchInvoiceDetails(newBooking.id);
  } else if (!props.show) {
    // Reset khi modal đóng
    invoiceDetails.value = [];
    selectedServiceId.value = null;
  }
}, { immediate: true, deep: true });

watch(() => props.show, (newVal) => {
  if (newVal && props.booking && props.booking.id) {
    fetchInvoiceDetails(props.booking.id);
    if (availableServices.value.length === 0) {
        fetchAvailableServices();
    }
  } else if (!newVal) { // Thêm else if để reset khi modal đóng bằng cách khác (vd: props.show đổi từ ngoài)
    invoiceDetails.value = [];
    selectedServiceId.value = null;
  }
});


const addServiceToInvoice = () => {
  if (!selectedServiceId.value) {
    toast.warn('Vui lòng chọn một dịch vụ.');
    return;
  }
  const serviceToAdd = availableServices.value.find(s => s.id === selectedServiceId.value);
  if (serviceToAdd) {
    invoiceDetails.value.push({
      id: null, // ID này sẽ được gán bởi backend nếu là item mới
      client_temp_id: Date.now() + Math.random(), // Unique key for v-for before saving
      service_id: serviceToAdd.id,
      name: serviceToAdd.name,
      price: serviceToAdd.price,
      quantity: 1, // Default quantity to 1 for new entries
      added_this_session_at: new Date(), // Timestamp for this session addition
    });
    selectedServiceId.value = null; // Reset lựa chọn
  }
};

const removeServiceFromInvoice = async (detail, index) => {
  const confirmation = confirm(`Bạn có chắc muốn xóa dịch vụ "${detail.name}" khỏi hóa đơn?`);
  if (!confirmation) return;

  if (detail.id) { // Nếu dịch vụ đã có trong CSDL (có ID)
    try {
      // Backend: InvoiceDetailService->deleteService(int $id, int $idInvoice)
      await api.delete(`/bookings/${props.booking.id}/invoice-details/${detail.id}`);
      toast.success(`Đã xóa dịch vụ "${detail.name}".`);
      invoiceDetails.value.splice(index, 1);
      emit('invoice-updated');
    } catch (error) {
      console.error('Error deleting service from invoice:', error);
      toast.error(`Không thể xóa dịch vụ "${detail.name}".`);
    }
  } else { // Nếu dịch vụ mới thêm ở client, chưa lưu vào CSDL
    invoiceDetails.value.splice(index, 1);
    toast.info(`Đã xóa dịch vụ "${detail.name}" (chưa lưu).`);
  }
};

const saveInvoice = async () => {
  if (!props.booking || !props.booking.id) {
    toast.error('Không có thông tin đặt phòng.');
    return;
  }
  isLoading.value = true;
  try {
    const payload = {
      invoice_details: invoiceDetails.value.map(d => ({
        id: d.id, // id của invoice_detail (nếu có, cho update)
        service_id: d.service_id,
        quantity: d.quantity,
      })),
    };
    // Backend: InvoiceDetailService->upSert(array $data, int $id)
    const response = await api.post(`/bookings/${props.booking.id}/invoice-details`, payload);
    invoiceDetails.value = response.data?.invoice_details || [];
    toast.success('Đã cập nhật hóa đơn thành công!');
    emit('invoice-updated');
    closeModal();
  } catch (error) {
    console.error('Error saving invoice:', error);
    const errorMessage = error.response?.data?.message || 'Không thể lưu hóa đơn.';
    toast.error(errorMessage);
  } finally {
    isLoading.value = false;
  }
};

const closeModal = () => {
  emit('close');
};

const totalAmount = computed(() => {
  return invoiceDetails.value.reduce((sum, detail) => {
    return sum + (parseFloat(detail.price) * parseInt(detail.quantity));
  }, 0);
});

onMounted(() => {
  fetchAvailableServices();
});

</script>

<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-labelledby="invoiceServiceModalLabel" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="invoiceServiceModalLabel">Quản lý Dịch vụ cho Hóa đơn - Đặt phòng #{{ booking?.id }}</h5>
          <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div v-if="isLoading" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Đang tải...</span>
            </div>
          </div>
          <div v-else>
            <!-- Phần thêm dịch vụ -->
            <div class="row mb-3 align-items-end">
              <div class="col-md-8">
                <label for="serviceSelect" class="form-label">Chọn dịch vụ:</label>
                <v-select
                  id="serviceSelect"
                  label="name"
                  :options="availableServices"
                  :reduce="service => service.id"
                  v-model="selectedServiceId"
                  placeholder="-- Tìm kiếm và chọn dịch vụ --"
                  class="form-control-vue-select"
                >
                  <template #option="option">
                    {{ option.name }} - {{ option.price?.toLocaleString() }} VND
                  </template>
                  <template #selected-option="option">
                    {{ option.name }} - {{ option.price?.toLocaleString() }} VND
                  </template>
                </v-select>
              </div>
              <div class="col-md-4">
                <button class="btn btn-success w-100 mt-auto" @click="addServiceToInvoice" :disabled="!selectedServiceId">
                  <i class="fas fa-plus"></i> Thêm vào HĐ
                </button>
              </div>
            </div>

            <!-- Danh sách dịch vụ đã thêm -->
            <h6>Danh sách dịch vụ đã chọn:</h6>
            <div v-if="invoiceDetails.length === 0" class="alert alert-info">
              Chưa có dịch vụ nào được thêm vào hóa đơn.
            </div>
            <table v-else class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>Tên Dịch Vụ</th>
                  <th style="width: 150px;">Đơn Giá</th>
                  <th style="width: 120px;">Số Lượng</th>
                  <th style="width: 180px;">Thời gian</th>
                  <th style="width: 150px;">Thành Tiền</th>
                  <th style="width: 80px;">Hành Động</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detail, index) in invoiceDetails" :key="detail.id || detail.client_temp_id">
                  <td>{{ detail.name }}</td>
                  <td>{{ parseFloat(detail.price)?.toLocaleString() }} VND</td>
                  <td>
                    <input type="number" class="form-control form-control-sm" v-model.number="detail.quantity" min="1" @change="detail.quantity = Math.max(1, detail.quantity || 1)">
                  </td>
                  <td>
                    {{ detail.added_this_session_at ? formatDateTime(detail.added_this_session_at) : (detail.created_at ? formatDateTime(detail.created_at) : 'N/A') }}
                  </td>
                  <td>{{ (parseFloat(detail.price) * parseInt(detail.quantity))?.toLocaleString() }} VND</td>
                  <td>
                    <button class="btn btn-danger btn-sm" @click="removeServiceFromInvoice(detail, index)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="text-end fw-bold">Tổng cộng:</td>
                  <td colspan="2" class="fw-bold">{{ totalAmount?.toLocaleString() }} VND</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">Đóng</button>
          <button type="button" class="btn btn-primary" @click="saveInvoice" :disabled="isLoading || invoiceDetails.length === 0">
            <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Lưu Hóa Đơn
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-dialog {
  max-width: 800px; /* Kích thước lớn hơn cho modal */
}
.table th, .table td {
  vertical-align: middle;
}
/* Thêm style nếu cần thiết */
/* Style to make vue-select look more like a Bootstrap form-control */
.form-control-vue-select ::v-deep(.vs__dropdown-toggle) {
  border-color: #ced4da;
  border-radius: 0.25rem; /* Bootstrap's default border-radius */
}

.form-control-vue-select.is-invalid ::v-deep(.vs__dropdown-toggle) {
  border-color: #dc3545; /* Bootstrap's danger color for invalid fields */
}

.form-control-vue-select ::v-deep(.vs__search::placeholder),
.form-control-vue-select ::v-deep(.vs__search) {
  margin-top: 0;
  padding-top: 0.375rem; /* Approximate Bootstrap padding */
  padding-bottom: 0.375rem;
}

.form-control-vue-select ::v-deep(.vs__selected) {
  padding-top: 0.1rem; /* Adjust padding for selected item to align better */
  padding-bottom: 0.1rem;
  font-size: 1rem; /* Match Bootstrap's default font size */
}

.form-control-vue-select ::v-deep(.vs__actions) {
  padding-top: 0.2rem; /* Adjust padding for clear/dropdown toggle icons */
}

</style> 