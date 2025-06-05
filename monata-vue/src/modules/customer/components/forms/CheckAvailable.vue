<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { api } from '../../lib/axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../../stores/auth'

const toast = useToast()
const router = useRouter()
const authStore = useAuthStore()

const props = defineProps<{
  modelValue: boolean
  initialRoomType?: number | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const isModalOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const showLoginConfirm = ref(false)

const closeModal = () => {
  isModalOpen.value = false
  resetForm()
}

interface RoomType {
    id: number
    name: string
    price: string
    image: string
}

interface Room {
    id: number
    name: string
    room_type: string
    price: string
    thumbnail_path: string
    properties: Array<{
        property_id: number
        name: string
        value: string
    }>
}

interface ApiResponse {
  roomTypes: RoomType[]
  data: Room[]
}

interface FormData {
  checkin_at: string
  checkout_at: string
  roomType: number | null
}

const formData = ref<FormData>({
  checkin_at: '',
  checkout_at: '',
  roomType: null
})

const errors = ref<Record<string, string[]>>({})
const isLoading = ref(false)
const isChecking = ref(false)
const roomTypes = ref<RoomType[]>([])
const availableRooms = ref<Room[]>([])
const selectedRooms = ref<number[]>([])

interface User {
  id: number
  name: string
  email: string
  phone: string
}

const user = ref<User | null>(null)

const fetchUserData = async () => {
  try {
    const response = await api.get('/profile')
    user.value = {
      id: response.data.data.id,
      name: response.data.data.name,
      email: response.data.data.email,
      phone: response.data.data.phone
    }
  } catch (error) {
    console.error('Error fetching user data:', error)
  }
}

const fetchRoomTypes = async () => {
  try {
    const response = await api.get<ApiResponse>('/user-home')
    roomTypes.value = response.data.roomTypes
  } catch (error) {
    console.error('Error fetching room types:', error)
  }
}

const resetForm = () => {
  formData.value = {
    checkin_at: '',
    checkout_at: '',
    roomType: null
  }
  errors.value = {}
  availableRooms.value = []
  selectedRooms.value = []
}

const formatDateTime = (dateTimeStr: string): string => {
  if (!dateTimeStr) return ''
  const date = new Date(dateTimeStr)
  return date.toISOString().slice(0, 16).replace('T', ' ')
}

const formatPrice = (price: string) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(parseFloat(price))
}

const checkAvailability = async (e: Event) => {
  e.preventDefault()
  errors.value = {}
  isChecking.value = true
  availableRooms.value = []

  try {
    const response = await api.post('/bookings/check-room-availability', {
      checkin_at: formatDateTime(formData.value.checkin_at),
      checkout_at: formatDateTime(formData.value.checkout_at),
      roomType: formData.value.roomType || null
    })

    if (response.data.data.length > 0) {
      availableRooms.value = response.data.data
    } else {
      errors.value = {
        availability: ['No rooms available for the selected time']
      }
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value = {
        general: ['An error occurred, please try again later']
      }
    }
  } finally {
    isChecking.value = false
  }
}

const toggleRoomSelection = (roomId: number) => {
  const index = selectedRooms.value.indexOf(roomId)
  if (index === -1) {
    selectedRooms.value.push(roomId)
  } else {
    selectedRooms.value.splice(index, 1)
  }
}

const bookSelectedRooms = async () => {
  if (selectedRooms.value.length === 0) {
    toast.warning('Please select at least one room to book')
    return
  }

  if (!authStore.authenticated) {
    toast.info('Please login to book rooms')
    closeModal()
    localStorage.setItem('pendingBooking', JSON.stringify({
      rooms: selectedRooms.value,
      checkin_at: formatDateTime(formData.value.checkin_at),
      checkout_at: formatDateTime(formData.value.checkout_at)
    }))
    router.push('/login')
    return
  }

  isLoading.value = true
  try {
    const bookingDetails = selectedRooms.value.map(roomId => ({
      room_id: roomId,
      checkin_at: formatDateTime(formData.value.checkin_at),
      checkout_at: formatDateTime(formData.value.checkout_at)
    }))

    const response = await api.post('/bookings', {
      user_id: user.value.id,
      guest_name: user.value.name,
      guest_email: user.value.email,
      guest_phone: user.value.phone,
      note: '',
      booking_details: bookingDetails
    })

    toast.success('Booking successful!')
    closeModal()
    router.push('/profile')
  } catch (error: any) {
    console.error('Booking error:', error.response?.data)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      if (errors) {
        Object.entries(errors).forEach(([field, messages]) => {
          if (Array.isArray(messages)) {
            messages.forEach(message => {
              toast.error(`${field}: ${message}`)
            })
          } else {
            toast.error(`${field}: ${messages}`)
          }
        })
      } else {
        toast.error(error.response.data.message || 'Dữ liệu không hợp lệ')
      }
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Đặt phòng thất bại. Vui lòng thử lại sau!')
    }
  } finally {
    isLoading.value = false
  }
}

const checkPendingBooking = () => {
  const pendingBooking = localStorage.getItem('pendingBooking')
  if (pendingBooking && authStore.authenticated) {
    const bookingData = JSON.parse(pendingBooking)
    formData.value = {
      checkin_at: bookingData.checkin_at,
      checkout_at: bookingData.checkout_at,
      roomType: null
    }
    selectedRooms.value = bookingData.rooms
    localStorage.removeItem('pendingBooking')
    isModalOpen.value = true
    checkAvailability(new Event('submit'))
  }
}

const handleBookRooms = () => {
  if (selectedRooms.value.length === 0) {
    toast.warning('Please select at least one room to book')
    return
  }

  if (!authStore.authenticated) {
    if (confirm('You need to login to book rooms. Would you like to login now?')) {
      localStorage.setItem('pendingBooking', JSON.stringify({
        rooms: selectedRooms.value,
        checkin_at: formatDateTime(formData.value.checkin_at),
        checkout_at: formatDateTime(formData.value.checkout_at),
        roomType: formData.value.roomType,
        availableRooms: availableRooms.value
      }))
      router.push('/login')
      return
    }
    return
  }

  bookSelectedRooms()
}

const restoreBookingState = () => {
  const pendingBooking = localStorage.getItem('pendingBooking')
  if (pendingBooking) {
    const bookingData = JSON.parse(pendingBooking)
    formData.value = {
      checkin_at: bookingData.checkin_at,
      checkout_at: bookingData.checkout_at,
      roomType: bookingData.roomType
    }
    availableRooms.value = bookingData.availableRooms
    selectedRooms.value = bookingData.rooms
    localStorage.removeItem('pendingBooking')
  }
}

watch(
  () => authStore.authenticated,
  (isAuthenticated) => {
    if (isAuthenticated) {
      fetchUserData()
      if (localStorage.getItem('pendingBooking')) {
        restoreBookingState()
      }
    } else {
      user.value = null
    }
  }
)

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue && props.initialRoomType) {
      formData.value.roomType = props.initialRoomType
    }
  }
)

onMounted(() => {
  fetchRoomTypes()
  if (authStore.authenticated) {
    fetchUserData()
  }
  if (authStore.authenticated && localStorage.getItem('pendingBooking')) {
    restoreBookingState()
  }
})
</script>

<template>
  <div 
    v-if="isModalOpen" 
    class="booking-modal-display" 
    @click="closeModal"
  >
    <form class="modal-content" @click.stop>
      <button type="button" class="btn-close-modal" @click="closeModal">
        <i class="fas fa-times"></i>
      </button>

      <!-- Form tìm kiếm -->
      <div class="modal-body" v-if="availableRooms.length === 0">
        <div class="row g-3">
          <!-- Check-in Date -->
          <div class="col-md-6">
            <label class="form-label">Check-in date</label>
            <input 
              class="form-control" 
              type="datetime-local" 
              v-model="formData.checkin_at"
              :class="{ 'is-invalid': errors.checkin_at }"
              required
              step="3600"
            />
            <div v-if="errors.checkin_at" class="invalid-feedback">
              {{ errors.checkin_at[0] }}
            </div>
          </div>

          <!-- Check-out Date -->
          <div class="col-md-6">
            <label class="form-label">Check-out date</label>
            <input 
              class="form-control" 
              type="datetime-local" 
              v-model="formData.checkout_at"
              :class="{ 'is-invalid': errors.checkout_at }"
              required
              step="3600"
            />
            <div v-if="errors.checkout_at" class="invalid-feedback">
              {{ errors.checkout_at[0] }}
            </div>
          </div>

          <!-- Room Type -->
          <div class="col-12">
            <label class="form-label">Room Type</label>
            <select 
              class="form-select" 
              v-model="formData.roomType"
              :class="{ 'is-invalid': errors.roomType }"
            >
              <option :value="null">Room Type</option>
              <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">
                {{ roomType.name }}
              </option>
            </select>
            <div v-if="errors.roomType" class="invalid-feedback">
              {{ errors.roomType[0] }}
            </div>
          </div>

          <!-- General Error -->
          <div v-if="errors.general || errors.availability" class="col-12">
            <div class="alert alert-danger">
              <div v-if="errors.general">{{ errors.general[0] }}</div>
              <div v-if="errors.availability">{{ errors.availability[0] }}</div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="col-12">
            <button 
              type="submit" 
              class="btn btn-primary w-100" 
              @click="checkAvailability"
              :disabled="isChecking"
            >
              {{ isChecking ? 'Checking...' : 'Check Availability' }}
            </button>
          </div>
        </div>
      </div>

      <div class="modal-body" v-else>
        <div class="row g-3">
          <div class="col-12">
            <div class="alert alert-info">
              {{ availableRooms.length }} available rooms found
            </div>
          </div>

          <div v-for="room in availableRooms" 
            :key="room.id" 
            class="col-md-4"
          >
            <div class="card h-100 room-card" 
              :class="{ 'border-primary': selectedRooms.includes(room.id) }"
              @click="toggleRoomSelection(room.id)"
            >
              <img :src="room.thumbnail_path" 
                :alt="room.name" 
                class="card-img-top room-thumbnail"
              >
              <div class="card-body">
                <h5 class="card-title">Room {{ room.name }}</h5>
                <p class="card-text text-muted small">{{ room.room_type }}</p>
                <p class="card-text">
                  <strong>{{ formatPrice(room.price) }}</strong> / night
                </p>
                <ul class="list-unstyled small">
                  <li v-for="property in room.properties" 
                    :key="property.property_id"
                    class="mb-1"
                  >
                    <i class="fas fa-check text-success me-2"></i>
                    {{ property.name }}: {{ property.value }}
                  </li>
                </ul>
              </div>
              <div class="card-footer bg-transparent py-2">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :checked="selectedRooms.includes(room.id)"
                    @click.stop
                    @change="toggleRoomSelection(room.id)"
                  >
                  <label class="form-check-label small">
                    Choose this room
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 mt-3">
            <div class="d-flex gap-2">
              <button 
                type="button" 
                class="btn btn-secondary flex-grow-1" 
                @click="availableRooms = []"
              >
                Back to search
              </button>
              <button 
                type="button" 
                class="btn btn-primary flex-grow-1" 
                @click="handleBookRooms"
                :disabled="isLoading || selectedRooms.length === 0"
              >
                {{ isLoading ? 'Please wait...' : `Proceed to book ${selectedRooms.length} selected rooms` }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<style scoped>
.booking-modal-display {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 95%;
  max-width: 1200px;
  max-height: 95vh;
  overflow-y: auto;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.modal-body {
  padding: 2.5rem;
  width: 100%;
  max-width: 1000px;
  margin: 0 auto;
}

.form-label {
  font-weight: 500;
  color: #333;
  margin-bottom: 0.5rem;
}

.form-control, .form-select {
  height: 45px;
  font-size: 1rem;
}

.card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 8px;
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.card-text {
  margin-bottom: 1rem;
}

.list-unstyled {
  margin-bottom: 1rem;
}

.btn {
  height: 45px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
}

.alert {
  text-align: center;
  font-weight: 500;
}

.btn-close-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #666;
  cursor: pointer;
  padding: 0.5rem;
  line-height: 1;
  z-index: 1;
  transition: color 0.2s ease;
}

.btn-close-modal:hover {
  color: #333;
}

.room-thumbnail {
  width: 100%;
  aspect-ratio: 16/9;
  object-fit: cover;
  border-radius: 8px 8px 0 0;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card.border-primary {
  border-width: 2px !important;
}

.form-check {
  margin: 0;
}

.form-check-input {
  cursor: pointer;
}

.gap-2 {
  gap: 0.5rem;
}

.flex-grow-1 {
  flex-grow: 1;
}

.room-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 8px;
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
  font-size: 0.9rem;
}

.room-thumbnail {
  width: 100%;
  aspect-ratio: 16/9;
  object-fit: cover;
  border-radius: 8px 8px 0 0;
}

.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.card-text {
  margin-bottom: 0.5rem;
}

.list-unstyled {
  margin-bottom: 0.5rem;
}

.list-unstyled li {
  font-size: 0.85rem;
}

.card-footer {
  border-top: 1px solid rgba(0,0,0,0.1);
  padding: 0.5rem 1rem;
}

.form-check {
  margin: 0;
}

.form-check-label {
  font-size: 0.85rem;
  color: #666;
}

.card.border-primary {
  border-width: 2px !important;
}
</style>
