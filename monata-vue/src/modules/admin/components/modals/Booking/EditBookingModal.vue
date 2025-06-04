<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';
import { api } from '@/modules/admin/lib/axios';
import moment from 'moment';
import { useToast } from 'vue-toastification';
import FlatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

const toast = useToast();

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  booking: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'bookingUpdated']);

// Form fields
const guestName = ref('');
const guestEmail = ref('');
const guestPhone = ref('');
const deposit = ref(0);
const bookingNote = ref('');
const bookingStatus = ref('PENDING');

// Booking details
const totalPrice = ref(0);
const bookedRooms = ref([]);
const newRoomsForBooking = ref([]);

// Room availability
const availableRooms = ref([]);
const searchCheckInTime = ref(moment().format('YYYY-MM-DD HH:mm'));
const searchCheckOutTime = ref(moment().add(1, 'day').format('YYYY-MM-DD HH:mm'));
const showRoomAvailabilitySection = ref(false);

const bookingId = computed(() => props.booking?.id || null);

const flatpickrConfigGlobal = {
  enableTime: true,
  dateFormat: "Y-m-d H:i",
  time_24hr: true,
  minuteIncrement: 30,
  allowInput: true,
};

watch(() => props.show, (newValue) => {
  if (newValue) {
    document.body.classList.add('modal-open-custom');
    if (props.booking) {
      populateForm(props.booking);
    }
  } else {
    document.body.classList.remove('modal-open-custom');
    resetForm();
  }
});

const populateForm = (bookingData) => {
  guestName.value = bookingData.guest_name || '';
  guestEmail.value = bookingData.guest_email || '';
  guestPhone.value = bookingData.guest_phone || '';
  deposit.value = bookingData.deposit_amount || 0;
  bookingNote.value = bookingData.note || '';
  bookingStatus.value = mapStatusNumberToString(bookingData.status) || 'PENDING';

  bookedRooms.value = bookingData.booking_details ? bookingData.booking_details.map(detail => ({
    id: detail.id,
    room_id: detail.room_id,
    room_name: detail.room_name,
    room_type: detail.room_type,
    checkin_at: moment(detail.checkin_at).format('YYYY-MM-DD HH:mm'),
    checkout_at: moment(detail.checkout_at).format('YYYY-MM-DD HH:mm'),
    price_per_day: parseFloat(detail.price_per_day) || 0,
    status: detail.status
  })) : [];
  newRoomsForBooking.value = [];
  recalculateTotalPrice();
};

const mapStatusNumberToString = (statusNumber) => {
  const statusMapping = {
    1: 'PENDING',
    2: 'CONFIRMED',
    3: 'CHECKED_IN',
    4: 'CHECKED_OUT',
    5: 'CANCELLED',
    6: 'NO_SHOW',
  };
  return statusMapping[statusNumber];
};

const resetForm = () => {
  guestName.value = '';
  guestEmail.value = '';
  guestPhone.value = '';
  totalPrice.value = 0;
  deposit.value = 0;
  bookingNote.value = '';
  bookingStatus.value = 'PENDING';
  bookedRooms.value = [];
  newRoomsForBooking.value = [];
  availableRooms.value = [];
  searchCheckInTime.value = moment().format('YYYY-MM-DD HH:mm');
  searchCheckOutTime.value = moment().add(1, 'day').format('YYYY-MM-DD HH:mm');
  showRoomAvailabilitySection.value = false;
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '0 VNĐ';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateTimeString, format = 'YYYY-MM-DD HH:mm') => {
  if (!dateTimeString) return '';
  return moment(dateTimeString).format(format);
};

const toggleAddRoomSection = () => {
  showRoomAvailabilitySection.value = !showRoomAvailabilitySection.value;
  if (showRoomAvailabilitySection.value) {
    availableRooms.value = [];
  }
};

const checkAvailability = async () => {
  if (!searchCheckInTime.value || !searchCheckOutTime.value) {
    toast.error('Vui lòng chọn thời gian check-in và check-out để tìm kiếm.');
    return;
  }
  try {
    const response = await api.post('/bookings/check-room-availability', {
      checkin_at: searchCheckInTime.value,
      checkout_at: searchCheckOutTime.value
    });
    availableRooms.value = response.data.data.map(apiRoom => ({
      id: apiRoom.id,
      name: apiRoom.name,
      room_type_from_api: apiRoom.room_type || 'N/A',
      price_from_api: apiRoom.price ? parseFloat(apiRoom.price) : 0,
      selected: false,
    }));
    if (availableRooms.value.length === 0) {
      toast.info('Không có phòng trống trong khoảng thời gian đã chọn.');
    }
  } catch (error) {
    console.error('Error checking room availability:', error);
    toast.error('Lỗi khi kiểm tra phòng trống: ' + (error.response?.data?.message || 'Unknown error.'));
    availableRooms.value = [];
  }
};

const selectedRoomsCount = computed(() => {
  return availableRooms.value.filter(room => room.selected).length;
});

const addSelectedRoomsToBooking = () => {
  const roomsToAdd = availableRooms.value.filter(room => room.selected);
  if (roomsToAdd.length === 0) {
    toast.warn('Vui lòng chọn ít nhất một phòng.');
    return;
  }

  roomsToAdd.forEach(room => {
    const alreadyNew = newRoomsForBooking.value.find(nr =>
      nr.room_id === room.id &&
      moment(nr.checkin_at).isSame(moment(searchCheckInTime.value)) &&
      moment(nr.checkout_at).isSame(moment(searchCheckOutTime.value))
    );
    const alreadyBooked = bookedRooms.value.find(br => br.room_id === room.id);

    if (!alreadyNew && !alreadyBooked) {
      newRoomsForBooking.value.push({
        room_id: room.id,
        room_name: room.name,
        room_type: room.room_type_from_api,
        checkin_at: searchCheckInTime.value,
        checkout_at: searchCheckOutTime.value,
        price_per_day: room.price_from_api,
      });
    } else if (alreadyBooked) {
      toast.info(`Phòng ${room.name} đã tồn tại trong danh sách phòng đã đặt.`);
    } else {
      toast.info(`Phòng ${room.name} với thời gian này đã được chọn.`);
    }
  });

  recalculateTotalPrice();
  toast.success(`${roomsToAdd.length} phòng đã được thêm vào danh sách chờ.`);
  availableRooms.value.forEach(room => room.selected = false);
};

const removeBookedRoom = (index, isNewList) => {
  if (isNewList) {
    newRoomsForBooking.value.splice(index, 1);
  } else {
    bookedRooms.value.splice(index, 1);
  }
  recalculateTotalPrice();
};

const calculateDuration = (checkin, checkout) => {
  if (!checkin || !checkout) return 1;
  const cin = moment(checkin);
  const cout = moment(checkout);
  if (!cin.isValid() || !cout.isValid() || cin.isSameOrAfter(cout)) return 1;

  const diffInHours = cout.diff(cin, 'hours');
  const unitHours = 12;
  return Math.max(1, Math.ceil(diffInHours / unitHours));
};

const recalculateTotalPrice = () => {
  let currentTotal = 0;
  bookedRooms.value.forEach(room => {
    const duration = calculateDuration(room.checkin_at, room.checkout_at);
    currentTotal += duration * room.price_per_day;
  });
  newRoomsForBooking.value.forEach(room => {
    const duration = calculateDuration(room.checkin_at, room.checkout_at);
    currentTotal += duration * room.price_per_day;
  });
  totalPrice.value = currentTotal;
};

const updateBooking = async () => {
  if (!bookingId.value) {
    toast.error('Không tìm thấy ID booking.');
    return;
  }

  const allBookingDetailsPayload = [
    ...bookedRooms.value.map(br => ({
      id: br.id,
      room_id: br.room_id,
      checkin_at: moment(br.checkin_at).format('YYYY-MM-DD HH:mm'),
      checkout_at: moment(br.checkout_at).format('YYYY-MM-DD HH:mm'),
      price_per_day: br.price_per_day,
    })),
    ...newRoomsForBooking.value.map(nr => ({
      room_id: nr.room_id,
      checkin_at: moment(nr.checkin_at).format('YYYY-MM-DD HH:mm'),
      checkout_at: moment(nr.checkout_at).format('YYYY-MM-DD HH:mm'),
      price_per_day: nr.price_per_day,
    }))
  ];

  if (allBookingDetailsPayload.length === 0) {
    toast.error('Booking phải có ít nhất một phòng.');
    return;
  }

  let finalTotalForPayload = 0;
  allBookingDetailsPayload.forEach(detail => {
    const duration = calculateDuration(detail.checkin_at, detail.checkout_at);
    finalTotalForPayload += duration * detail.price_per_day;
  });

  const payload = {
    guest_name: guestName.value,
    guest_email: guestEmail.value,
    guest_phone: guestPhone.value,
    deposit: parseFloat(deposit.value) || 0,
    note: bookingNote.value,
    total_payment: finalTotalForPayload,
    booking_details: allBookingDetailsPayload,
    user_id: props.booking?.user_id
  };

  try {
    await api.put(`/bookings/${bookingId.value}`, payload);
    toast.success('Booking đã được cập nhật thành công!');
    emit('bookingUpdated');
    closeModal();
  } catch (error) {
    console.error('Error updating booking:', error.response?.data || error.message);
    const errorMsg = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join(' ')
      : (error.response?.data?.message || 'Lỗi không xác định.');
    toast.error(`Cập nhật booking thất bại: ${errorMsg}`);
  }
};

const closeModal = () => {
  emit('close');
};

onMounted(() => {
  if (props.show) {
    if (props.booking) {
      populateForm(props.booking);
    }
  }
});

</script>

<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" @click.stop="">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Cập Nhật Booking</h5>
          <button type="button" class="btn-close btn-close-white" @click="closeModal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="updateBookingForm" @submit.prevent="updateBooking">
            <h5 class="mb-3">Thông tin Khách hàng</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="editGuestName" class="form-label">Khách hàng</label>
                <input type="text" class="form-control" id="editGuestName" v-model="guestName">
              </div>
              <div class="col-md-6">
                <label for="editGuestEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editGuestEmail" v-model="guestEmail">
              </div>
              <div class="col-md-6">
                <label for="editGuestPhone" class="form-label">Điện thoại</label>
                <input type="tel" class="form-control" id="editGuestPhone" v-model="guestPhone">
              </div>
            </div>

            <h5 class="mb-3">Thông tin Booking</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="editTotalPrice" class="form-label">Tổng giá</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="editTotalPrice" :value="formatCurrency(totalPrice)"
                    disabled>
                </div>
              </div>
              <div class="col-md-6">
                <label for="editDeposit" class="form-label">Tiền đặt cọc</label>
                <div class="input-group">
                  <input type="number" class="form-control" id="editDeposit" v-model.number="deposit" min="0">
                  <span class="input-group-text">VNĐ</span>
                </div>
              </div>

              <div class="col-12">
                <label for="editBookingNote" class="form-label">Ghi chú</label>
                <textarea class="form-control" id="editBookingNote" rows="3" v-model="bookingNote"></textarea>
              </div>
            </div>

            <h5 class="mb-3">Phòng đã đặt</h5>
            <div class="table-responsive mb-4">
              <table class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th scope="col">Phòng</th>
                    <th scope="col">Loại phòng</th>
                    <th scope="col">Check-in</th>
                    <th scope="col">Check-out</th>
                    <th scope="col">Giá nửa ngày</th>
                    <th scope="col">Thao tác</th>
                  </tr>
                </thead>
                <tbody id="currentBookedRoomsTableBody">
                  <tr v-for="(room, index) in bookedRooms" :key="room.id || `existing-${index}`">
                    <td>{{ room.room_name }}</td>
                    <td>{{ room.room_type }}</td>
                    <td>{{ formatDate(room.checkin_at) }}</td>
                    <td>{{ formatDate(room.checkout_at) }}</td>
                    <td>{{ formatCurrency(room.price_per_day) }}</td>
                    <td>
                      <button type="button" class="btn btn-sm btn-outline-danger"
                        @click="removeBookedRoom(index, false)"
                        :disabled="room.status !== 1 && room.status !== undefined">Xóa</button>
                    </td>
                  </tr>
                  <tr v-for="(room, index) in newRoomsForBooking" :key="`new-${room.room_id}-${index}`">
                    <td>{{ room.room_name }}</td>
                    <td>{{ room.room_type }}</td>
                    <td>{{ formatDate(room.checkin_at) }}</td>
                    <td>{{ formatDate(room.checkout_at) }}</td>
                    <td>{{ formatCurrency(room.price_per_day) }}</td>
                    <td>
                      <button type="button" class="btn btn-sm btn-outline-danger"
                        @click="removeBookedRoom(index, true)">Xóa</button>
                    </td>
                  </tr>
                  <tr v-if="bookedRooms.length === 0 && newRoomsForBooking.length === 0">
                    <td colspan="6" class="text-center">Chưa có phòng nào được chọn.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mb-4">
              <button type="button" class="btn btn-primary" @click="toggleAddRoomSection">
                {{ showRoomAvailabilitySection ? 'Ẩn phần thêm phòng' : 'Thêm/Thay đổi phòng' }}
              </button>
            </div>

            <div v-if="showRoomAvailabilitySection" id="roomAvailabilitySection"
              class="p-3 border rounded bg-light mb-4">
              <h5 class="mb-3">Kiểm tra phòng khả dụng</h5>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="searchCheckInTimeInput" class="form-label">Check-in Time:</label>
                  <FlatPickr v-model="searchCheckInTime" :config="flatpickrConfigGlobal" class="form-control"
                    placeholder="Chọn ngày giờ check-in" id="searchCheckInTimeInput" />
                </div>
                <div class="col-md-6">
                  <label for="searchCheckOutTimeInput" class="form-label">Check-out Time:</label>
                  <FlatPickr v-model="searchCheckOutTime" :config="flatpickrConfigGlobal" class="form-control"
                    placeholder="Chọn ngày giờ check-out" id="searchCheckOutTimeInput" />
                </div>
              </div>
              <div class="mb-3">
                <button type="button" class="btn btn-info" @click="checkAvailability">Check Availability</button>
              </div>

              <div v-if="availableRooms.length > 0">
                <h6 class="mb-3">Chọn phòng cho Booking này:</h6>
                <div class="table-responsive">
                  <table class="table table-bordered align-middle">
                    <thead>
                      <tr>
                        <th width="80px"></th>
                        <th scope="col">Tên phòng</th>
                        <th scope="col">Loại phòng</th>
                        <th scope="col">Giá nửa ngày</th>
                      </tr>
                    </thead>
                    <tbody id="availableRoomsTableBody">
                      <tr v-for="room in availableRooms" :key="room.id">
                        <td class="text-center">
                          <input type="checkbox" class="form-check-input" v-model="room.selected">
                        </td>
                        <td>{{ room.name }}</td>
                        <td>{{ room.room_type_from_api }}</td>
                        <td>{{ formatCurrency(room.price_from_api) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p class="mt-2 mb-0">Bạn đã chọn <span id="selectedRoomsCountSpan">{{ selectedRoomsCount }}</span>
                  phòng.</p>
                <button type="button" class="btn btn-success mt-3" @click="addSelectedRoomsToBooking">Thêm phòng đã
                  chọn</button>
              </div>
              <p v-else-if="!searchCheckInTime || !searchCheckOutTime" class="text-muted">Vui lòng chọn thời gian để
                kiểm tra phòng.</p>
              <p v-else class="text-muted">Không có phòng nào trống hoặc chưa tìm kiếm.</p>
            </div>

            <div class="modal-footer mt-4">
              <button type="button" class="btn btn-secondary" @click="closeModal">Hủy</button>
              <button type="submit" class="btn btn-success">Lưu thay đổi</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-dialog {
  background: white;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, .3);
  width: 90%;
  max-width: 1200px;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-content {
  display: flex;
  flex-direction: column;
  max-height: inherit;
  overflow: hidden;
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.modal-title {
  margin-bottom: 0;
  line-height: 1.5;
}

.modal-body {
  position: relative;
  flex: 1 1 auto;
  padding: 1rem;
  overflow-y: auto;
}

.modal-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  flex-shrink: 0;
}

body.modal-open-custom {
  overflow: hidden;
}

.btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
}
</style>
