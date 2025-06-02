<script setup>
import { ref, computed, onMounted } from 'vue'; // watch for body class handled in modals
import { api } from '../lib/axios';
import moment from 'moment';
import 'moment/locale/en-gb';
moment.locale('en-gb');
import { useToast } from 'vue-toastification';
const toast = useToast();

import BookingDetailModal from '../components/modals/Booking/BookingDetailModal.vue';
import CreateBookingModal from '../components/modals/Booking/CreateBookingModal.vue';

const apiUrl = import.meta.env.VITE_API_URL;

const rawBookings = ref([]);
const dailyRoomBookings = ref({});
const currentMonth = ref(moment().month());
const currentYear = ref(moment().year());
const selectedDate = ref(null);

const showBookingDetailModal = ref(false);
const selectedBookingDetail = ref(null);

const showCreateBookingModal = ref(false);

const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const fetchBookings = async () => {
  try {
    const response = await api.get(`/bookings`);
    rawBookings.value = response.data?.data || [];
    processBookings(rawBookings.value);
  } catch (error) {
    console.error('Error fetching bookings:', error);
    toast.error('Failed to fetch bookings.');
  }
};

onMounted(() => {
  fetchBookings();
  const today = moment().startOf('day').toISOString();
  selectedDate.value = today;
});

const processBookings = (bookingsData) => {
  const processedBookings = {};
  bookingsData.forEach(booking => {
    if (booking.booking_details && Array.isArray(booking.booking_details)) {
      booking.booking_details.forEach(detail => {
        if (!detail.checkin_at || !detail.checkout_at) {
          console.warn(`Booking detail ID ${detail.id} (Booking ID: ${booking.id}) missing checkin_at or checkout_at, skipping.`);
          return;
        }

        const checkinMoment = moment(detail.checkin_at);
        const checkoutMoment = moment(detail.checkout_at);

        const startDateForBooking = checkinMoment.clone().startOf('day');
        let endDateForBooking = checkoutMoment.clone().subtract(1, 'second').startOf('day');

        if (checkoutMoment.hour() > 0 || checkoutMoment.minute() > 0 || checkoutMoment.second() > 0) {
          endDateForBooking = checkoutMoment.clone().startOf('day');
        }

        let currentDate = moment(startDateForBooking);
        while (currentDate.isSameOrBefore(endDateForBooking)) {
          const dateKey = currentDate.toISOString();
          if (!processedBookings[dateKey]) {
            processedBookings[dateKey] = {};
          }
          processedBookings[dateKey][detail.room_id] = {
            booking_id: booking.id,
            guest_name: booking.guest_name,
            room_id: detail.room_id,
            room_name: detail.room_name,
            fullBooking: booking
          };
          currentDate.add(1, 'day');
        }
      });
    } else {
      console.log(`Booking ID ${booking.id} skipped due to missing booking_details.`);
    }
  });
  dailyRoomBookings.value = processedBookings;
};

const currentMonthYear = computed(() => {
  return moment().month(currentMonth.value).year(currentYear.value).format('MMMM[ ]YYYY');
});

const calendarDays = computed(() => {
  const startOfMonth = moment().month(currentMonth.value).year(currentYear.value).startOf('month');
  const endOfMonth = moment().month(currentMonth.value).year(currentYear.value).endOf('month');
  const startDay = startOfMonth.clone().startOf('isoWeek');
  const endDay = endOfMonth.clone().endOf('isoWeek');

  const days = [];
  let day = moment(startDay);

  while (day.isSameOrBefore(endDay)) {
    days.push({
      date: day.toISOString(),
      dayOfMonth: day.date(),
      isCurrentMonth: day.month() === currentMonth.value,
    });
    day.add(1, 'day');
  }
  return days;
});

const getDailyBookedRooms = (date) => {
  const dateKey = moment(date).startOf('day').toISOString();
  const roomsBookedOnDate = dailyRoomBookings.value[dateKey];
  if (!roomsBookedOnDate) {
    return [];
  }
  return Object.values(roomsBookedOnDate).sort((a, b) => a.room_id - b.room_id);
};

const isToday = (date) => {
  return moment(date).isSame(moment(), 'day');
};

const isSelectedDay = (date) => {
  return selectedDate.value && moment(date).isSame(moment(selectedDate.value), 'day');
};

const selectDay = (date) => {
  selectedDate.value = moment(date).startOf('day').toISOString();
};

const formattedSelectedDate = computed(() => {
  return selectedDate.value ? moment(selectedDate.value).format('DD MMMM[ ]YYYY') : '';
});

const bookedRoomsForSelectedDay = computed(() => {
  const uniqueBookings = new Map();
  getDailyBookedRooms(selectedDate.value).forEach(roomBooking => {
    uniqueBookings.set(roomBooking.booking_id, roomBooking.fullBooking);
  });
  return Array.from(uniqueBookings.values());
});

const prevMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
  selectedDate.value = null;
  fetchBookings();
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
  selectedDate.value = null;
  fetchBookings();
};

const getStatusText = (status) => {
  switch (status) {
    case 1: return 'PENDING';
    case 2: return 'CONFIRMED';
    case 3: return 'CHECKED IN';
    case 4: return 'CHECKED OUT';
    case 5: return 'CANCELLED';
    case 6: return 'NO SHOW';
    default: return 'UNDEFINED';
  }
};
const getBadgeClass = (status) => {
  switch (status) {
    case 1: return 'bg-secondary'; // PENDING
    case 2: return 'bg-primary';   // CONFIRMED
    case 3: return 'bg-success';   // CHECKED IN
    case 4: return 'bg-warning';   // CANCELLED
    case 5: return 'bg-danger';    // NO SHOW
    case 6: return 'bg-info';      // CHECKED OUT
    default: return 'bg-dark';     // Others
  }
};

const openBookingDetailModal = (booking) => {
  selectedBookingDetail.value = booking;
  showBookingDetailModal.value = true;
};

const closeBookingDetailModal = () => {
  showBookingDetailModal.value = false;
  selectedBookingDetail.value = null;
};

const handleBookingConfirmed = () => {
  fetchBookings(); // Re-fetch bookings after confirmation
};

const handleEditBooking = (booking) => {
  console.log('Editing booking:', booking);
  toast.info('Edit functionality is not yet implemented.');
};

// Functions for CreateBookingModal
const openCreateBookingModal = () => {
  showCreateBookingModal.value = true;
};

const closeCreateBookingModal = () => {
  showCreateBookingModal.value = false;
};

const handleBookingCreated = () => {
  fetchBookings(); // Re-fetch bookings after new booking is created
};
</script>

<template>
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Manage Bookings</h6>
      <button class="btn btn-primary" @click="openCreateBookingModal">Create New Booking</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <div class="card">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <button class="btn btn-light" @click="prevMonth">&lt; Prev</button>
            <h3>{{ currentMonthYear }}</h3>
            <button class="btn btn-light" @click="nextMonth">Next &gt;</button>
          </div>
          <div class="card-body">
            <div class="calendar-grid">
              <div v-for="dayName in dayNames" :key="dayName" class="calendar-header-day">
                {{ dayName }}
              </div>
              <div v-for="day in calendarDays" :key="day.date"
                :class="['calendar-day', { 'current-month': day.isCurrentMonth, 'today': isToday(day.date), 'selected-day': isSelectedDay(day.date) }]"
                @click="selectDay(day.date)">
                <div class="day-number">{{ day.dayOfMonth }}</div>
                <div class="room-summary mt-2">
                  <span v-for="roomBooking in getDailyBookedRooms(day.date)" :key="roomBooking.room_id"
                    class="badge text-white me-1" :class="getBadgeClass(roomBooking.fullBooking?.status)"
                    :title="`Room ${roomBooking.room_name} (${roomBooking.guest_name})`"
                    @click.stop="openBookingDetailModal(roomBooking.fullBooking)">
                    {{ roomBooking.room_name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="selectedDate" class="card mt-4">
          <div class="card-header bg-secondary text-white">
            <h3>Booking for: {{ formattedSelectedDate }}</h3>
          </div>
          <div class="card-body">
            <div v-if="bookedRoomsForSelectedDay.length > 0">
              <div v-for="booking in bookedRoomsForSelectedDay" :key="booking.id" class="card mb-3">
                <div class="card-body">
                  <p class="card-text">
                    Guest: <strong>{{ booking.guest_name }}</strong>
                  </p>
                  <p class="card-text">
                    Email: <strong>{{ booking.guest_email }}</strong>
                  </p>
                  <p class="card-text">
                    Phone: <strong>{{ booking.guest_phone }}</strong>
                  </p>
                  <p class="card-text">
                    Status: <strong>{{ getStatusText(booking.status) }}</strong>
                  </p>
                  <button v-if="booking.status === 1" class="btn btn-success btn-sm mr-2"
                    @click="openBookingDetailModal(booking)">Confirm</button>
                  <button class="btn btn-info btn-sm" @click="openBookingDetailModal(booking)">Detail</button>
                </div>
              </div>
            </div>
            <p v-else class="text-muted">No booking.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <BookingDetailModal :show="showBookingDetailModal" :booking="selectedBookingDetail" @close="closeBookingDetailModal"
    @booking-confirmed="handleBookingConfirmed" @edit-booking="handleEditBooking" />

  <CreateBookingModal :show="showCreateBookingModal" :initial-checkin-date="selectedDate"
    @close="closeCreateBookingModal" @booking-created="handleBookingCreated" />
</template>

<style scoped>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  border: 1px solid #ccc;
  border-radius: 5px;
  overflow: hidden;
}

.calendar-header-day {
  background-color: #f8f9fa;
  padding: 10px;
  text-align: center;
  font-weight: bold;
  border-bottom: 1px solid #eee;
}

.calendar-day {
  min-height: 120px;
  padding: 10px;
  border: 1px solid #eee;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  cursor: pointer;
  background-color: #fff;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.calendar-day:hover {
  background-color: #f0f0f0;
}

.calendar-day.current-month {
  background-color: #fff;
}

.calendar-day:not(.current-month) {
  background-color: #e9ecef;
  color: #6c757d;
}

.calendar-day.today {
  background-color: #e0f2f7;
  border: 2px solid #007bff;
}

.calendar-day.selected-day {
  background-color: #d1ecf1;
  border: 2px solid #17a2b8;
}

.day-number {
  font-weight: bold;
  font-size: 1.2em;
  margin-bottom: 5px;
}

.room-summary {
  margin-top: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.room-summary .badge {
  padding: 0.4em 0.6em;
  font-size: 0.75em;
  border-radius: 0.25rem;
  cursor: pointer;
}

.card-header h3 {
  margin: 0;
}
</style>
