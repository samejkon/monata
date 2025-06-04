<script setup lang="ts">
import { useModal, provideModal } from '../../composables/useModal'
import { ref, onMounted } from 'vue'
import { api } from '../../lib/axios'

const { isModalOpen, closeModal } = useModal()

interface RoomType {
    id: number
    name: string
    price: string
    image: string
}

interface ApiResponse {
  roomTypes: RoomType[]
}

const roomTypes = ref<RoomType[]>([])

const fetchRoomTypes = async () => {
  const response = await api.get<ApiResponse>('/user-home')
  roomTypes.value = response.data.roomTypes
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
    <form class="modal-content row" @click.stop>
      <div class="modal-item1">
        <h3 class="modal-heading">Check Availability</h3>
      </div>
      <div class="modal-item2">
        <input class="modal-input" type="date" name="checkInDate" id="checkInDate" placeholder="Check in date" />
      </div>
      <div class="modal-item3">
        <input class="modal-input" type="date" name="checkOutDate" id="checkOutDate" placeholder="Check out date" />
      </div>
      <div class="modal-item6">
        <select class="modal-input form-select" name="roomType" id="roomType">
          <option value="0">Room Type</option>
          <option v-for="roomType in roomTypes" :value="roomType.id">{{ roomType.name }}</option>
        </select>
      </div>
      <!-- <div class="modal-item4">
        <input class="modal-input" type="text" name="adult" id="adult" placeholder="Adult" readonly />
      </div>
      <div class="modal-item5">
        <input class="modal-input" type="text" name="children" id="children" placeholder="Children" readonly />
      </div> -->
      <div class="modal-item7">
        <button type="submit">Check Availability</button>
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

.modal-item7 button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.modal-item7 button:hover {
  background-color: #0056b3;
}
</style>
