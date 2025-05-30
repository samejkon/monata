<template>
    <div>
        <h2>Rooms</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <div v-for="room in records" :key="room.id" class="col mb-4" @click="openRoomDetailsModal(room)"
                style="cursor: pointer;">
                <div class="card h-100 shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img :src="room.thumbnail_path" :alt="room.name"
                                class="img-fluid rounded-start rounded-2 shadow-sm"
                                style="object-fit: cover; height: 100%;">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title">{{ room.name }}</h5>
                                <p class="card-text">{{ room.room_type }}</p>
                                <p class="card-text">
                                    <span
                                        :class="{ 'badge bg-success text-white': room.status === 1, 'badge bg-secondary text-white': room.status !== 1 }">
                                        {{ room.status === 1 ? 'Đang hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" :class="{ show: isModalVisible, 'd-block': isModalVisible }" tabindex="-1"
            aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roomDetailsModalLabel" v-if="roomDetails">{{ roomDetails.name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            @click="closeRoomDetailsModal"></button>
                    </div>
                    <div class="modal-body" v-if="roomDetails">
                        <div id="roomImagesCarousel" class="carousel slide mb-3 rounded-2 shadow-sm"
                            v-if="roomDetails.images && roomDetails.images.length > 0">
                            <div class="carousel-indicators">
                                <button type="button" v-for="(image, index) in roomDetails.images" :key="image.id"
                                    :data-bs-target="'#roomImagesCarousel'" :data-bs-slide-to="index"
                                    :class="{ active: index === 0 }" :aria-current="index === 0 ? 'true' : 'false'"
                                    :aria-label="'Slide ' + (index + 1)"></button>
                            </div>
                            <div class="carousel-inner rounded-2">
                                <div v-for="(image, index) in roomDetails.images" :key="image.id" class="carousel-item"
                                    :class="{ active: index === 0 }">
                                    <img :src="image.image_path" class="d-block w-100 rounded-2"
                                        :alt="'Image ' + (index + 1)">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#roomImagesCarousel"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#roomImagesCarousel"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                        <img v-else-if="roomDetails.thumbnail_path" :src="roomDetails.thumbnail_path"
                            :alt="roomDetails.name" class="img-fluid mb-3 rounded-2 shadow-sm">

                        <p><strong>Loại phòng:</strong> {{ roomDetails.room_type }}</p>
                        <p v-if="roomDetails.description"><strong>Mô tả:</strong> {{ roomDetails.description }}</p>
                        <p v-if="roomDetails.price"><strong>Giá:</strong> {{ roomDetails.price }}</p>
                        <p><strong>Trạng thái:</strong>
                            <span
                                :class="{ 'badge bg-success text-white': roomDetails.status === 1, 'badge bg-secondary text-white': roomDetails.status !== 1 }">
                                {{ roomDetails.status === 1 ? 'Đang hoạt động' : 'Không hoạt động' }}
                            </span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            @click="closeRoomDetailsModal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="isModalVisible" class="modal-backdrop fade show"></div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const apiUrl = import.meta.env.VITE_API_URL;
const records = ref([]);

// State cho modal
const isModalVisible = ref(false);
const selectedRoomId = ref<number | null>(null); // Lưu ID của phòng được chọn
const roomDetails = ref<any | null>(null); // Lưu thông tin chi tiết của phòng

const fetchRooms = async () => {
    try {
        const response = await axios.get(`${apiUrl}/admin/rooms`);
        records.value = response.data.data;
    } catch (error) {
        console.error('Error fetching rooms:', error);
        records.value = [];
    }
};

const fetchRoomDetails = async (roomId: number) => {
    try {
        const response = await axios.get(`${apiUrl}/admin/rooms/${roomId}`);
        roomDetails.value = response.data.data;
    } catch (error) {
        console.error(`Error fetching details for room ${roomId}:`, error);
        roomDetails.value = null;
    }
};

const openRoomDetailsModal = (room: any) => {
    selectedRoomId.value = room.id;
    roomDetails.value = null; // Reset thông tin chi tiết trước khi gọi API
    isModalVisible.value = true;
    fetchRoomDetails(room.id); // Gọi API khi mở modal
};

const closeRoomDetailsModal = () => {
    isModalVisible.value = false;
    selectedRoomId.value = null;
    roomDetails.value = null; // Reset thông tin chi tiết khi đóng modal
};

onMounted(() => {
    fetchRooms();
});
</script>

<style scoped>
/* Không cần nhiều CSS tùy chỉnh cho modal nữa vì Bootstrap đã cung cấp */
</style>
