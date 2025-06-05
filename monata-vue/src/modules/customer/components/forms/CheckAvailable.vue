<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { api } from '../../lib/axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const isModalOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

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

interface ApiResponse {
  roomTypes: RoomType[]
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
const roomTypes = ref<RoomType[]>([])

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
}

const formatDateTime = (dateTimeStr: string): string => {
  if (!dateTimeStr) return ''
  const date = new Date(dateTimeStr)
  return date.toISOString().slice(0, 16).replace('T', ' ')
}

const handleSubmit = async (e: Event) => {
  e.preventDefault()
  errors.value = {}
  isLoading.value = true

  try {
    const response = await api.post('/bookings/check-room-availability', {
      checkin_at: formatDateTime(formData.value.checkin_at),
      checkout_at: formatDateTime(formData.value.checkout_at),
      roomType: formData.value.roomType || null
    })

    console.log(response)

    if (response.data.data.length > 0) {
      router.push({
        name: 'Rooms',
        query: {
          checkin_at: formatDateTime(formData.value.checkin_at),
          checkout_at: formatDateTime(formData.value.checkout_at),
          roomType: formData.value.roomType || ''
        }
      })
      closeModal()
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
    isLoading.value = false
  }
}

onMounted(() => {
  fetchRoomTypes()
})
</script>

<template>
  <div 
    v-if="isModalOpen" 
    class="booking-modal-display" 
    @click="closeModal"
  >
    <form class="modal-content row" @click.stop @submit="handleSubmit">
      <div class="modal-item1">
        <h3 class="modal-heading">Check Availability</h3>
      </div>

      <!-- Check-in Date -->
      <div class="modal-item2">
        <input 
          class="modal-input" 
          type="datetime-local" 
          v-model="formData.checkin_at"
          :class="{ 'is-invalid': errors.checkin_at }"
          required
          step="3600"
        />
        <div v-if="errors.checkin_at" class="error-message">
          {{ errors.checkin_at[0] }}
        </div>
      </div>

      <!-- Check-out Date -->
      <div class="modal-item3">
        <input 
          class="modal-input" 
          type="datetime-local" 
          v-model="formData.checkout_at"
          :class="{ 'is-invalid': errors.checkout_at }"
          required
          step="3600"
        />
        <div v-if="errors.checkout_at" class="error-message">
          {{ errors.checkout_at[0] }}
        </div>
      </div>

      <!-- Room Type -->
      <div class="modal-item6">
        <select 
          class="modal-input form-select" 
          v-model="formData.roomType"
          :class="{ 'is-invalid': errors.roomType }"
        >
          <option :value="null">Room Type</option>
          <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">
            {{ roomType.name }}
          </option>
        </select>
        <div v-if="errors.roomType" class="error-message">
          {{ errors.roomType[0] }}
        </div>
      </div>

      <!-- General Error -->
      <div v-if="errors.general || errors.availability" class="modal-error">
        <div v-if="errors.general" class="error-message">
          {{ errors.general[0] }}
        </div>
        <div v-if="errors.availability" class="error-message">
          {{ errors.availability[0] }}
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-item7">
        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Đang kiểm tra...' : 'Check Availability' }}
        </button>
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
  padding: 20px;
  border-radius: 8px;
  min-width: 300px;
}

.modal-input {
  width: 100%;
  padding: 8px;
  margin: 8px 0;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.modal-input.is-invalid {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.modal-error {
  margin: 10px 0;
  padding: 10px;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
}

.modal-item7 button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.modal-item7 button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.modal-item7 button:hover:not(:disabled) {
  background-color: #0056b3;
}
</style>
