<template>
    <div>
        <h2>Rooms</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <div v-for="room in records" :key="room.id" class="col mb-4 room-card" @click="openRoomDetailsModal(room)">
                <div class="card h-100 shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-8">
                            <img :src="room.thumbnail_path" :alt="room.name"
                                class="img-fluid rounded-start rounded-2 shadow-sm room-thumbnail">
                        </div>
                        <div class="col-md-4">
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
            <div class="modal-dialog modal-dialog-centered modal-lg w-100">
                <div class="modal-content shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roomDetailsModalLabel" v-if="roomDetails">{{ roomDetails.name }}
                        </h5>
                    </div>
                    <div class="modal-body" v-if="roomDetails">
                        <div v-if="roomDetails.images && roomDetails.images.length > 0" class="room-images-container">
                            <img :src="currentLargeImage" :alt="roomDetails.name"
                                class="room-large-image img-fluid rounded-2 shadow-sm mb-2">
                            <div class="room-thumbnails">
                                <img v-for="(image, index) in roomDetails.images" :key="image.id"
                                    :src="image.image_path" :alt="'Thumbnail ' + (index + 1)"
                                    class="room-thumbnail-item img-thumbnail rounded-2 shadow-sm"
                                    @click="changeLargeImage(image.image_path)"
                                    :class="{ 'active-thumbnail': currentLargeImage === image.image_path }">
                            </div>
                        </div>
                        <img v-else-if="roomDetails.thumbnail_path" :src="roomDetails.thumbnail_path"
                            :alt="roomDetails.name" class="img-fluid mb-3 rounded-2 shadow-sm room-single-thumbnail">

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
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const apiUrl = import.meta.env.VITE_API_URL;
const records = ref([]);

const isModalVisible = ref(false);
const roomDetails = ref<any | null>(null);
const currentLargeImage = ref<string | null>(null);
const currentImageIndex = ref(0);
const isTransitioning = ref(false);
let intervalId: NodeJS.Timeout | null = null;

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
        if (roomDetails.value?.images?.length > 0) {
            currentLargeImage.value = roomDetails.value.images[0].image_path;
            currentImageIndex.value = 0;
            startImageSlider();
        } else if (roomDetails.value?.thumbnail_path) {
            currentLargeImage.value = roomDetails.value.thumbnail_path;
            stopImageSlider();
        } else {
            currentLargeImage.value = null;
            stopImageSlider();
        }
    } catch (error) {
        console.error(`Error fetching details for room ${roomId}:`, error);
        roomDetails.value = null;
        currentLargeImage.value = null;
        stopImageSlider();
    }
};

const openRoomDetailsModal = (room: any) => {
    roomDetails.value = null;
    isModalVisible.value = true;
    fetchRoomDetails(room.id);
};

const closeRoomDetailsModal = () => {
    isModalVisible.value = false;
    roomDetails.value = null;
    currentLargeImage.value = null;
    currentImageIndex.value = 0;
    stopImageSlider();
    isTransitioning.value = false;
};

const changeLargeImage = (imagePath: string) => {
    if (isTransitioning.value || currentLargeImage.value === imagePath) {
        return;
    }

    isTransitioning.value = true;
    const largeImageElement = document.querySelector('.room-large-image');
    if (largeImageElement) {
        largeImageElement.classList.add('fade-out');

        setTimeout(() => {
            currentLargeImage.value = imagePath;
            currentImageIndex.value = roomDetails.value.images.findIndex((img: any) => img.image_path === imagePath);
            largeImageElement.classList.remove('fade-out');
            isTransitioning.value = false;
        }, 300);
    } else {
        currentLargeImage.value = imagePath;
        currentImageIndex.value = roomDetails.value.images.findIndex((img: any) => img.image_path === imagePath);
        isTransitioning.value = false;
    }
};

const nextImage = () => {
    if (roomDetails.value?.images?.length > 1 && !isTransitioning.value) {
        isTransitioning.value = true;
        const largeImageElement = document.querySelector('.room-large-image');
        if (largeImageElement) {
            largeImageElement.classList.add('fade-out');
            setTimeout(() => {
                currentImageIndex.value = (currentImageIndex.value + 1) % roomDetails.value.images.length;
                currentLargeImage.value = roomDetails.value.images[currentImageIndex.value].image_path;
                largeImageElement.classList.remove('fade-out');
                isTransitioning.value = false;
            }, 300);
        } else {
            currentImageIndex.value = (currentImageIndex.value + 1) % roomDetails.value.images.length;
            currentLargeImage.value = roomDetails.value.images[currentImageIndex.value].image_path;
            isTransitioning.value = false;
        }
    }
};

const startImageSlider = () => {
    if (roomDetails.value?.images?.length > 1 && !intervalId) {
        intervalId = setInterval(nextImage, 5000);
    }
};

const stopImageSlider = () => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

onMounted(() => {
    fetchRooms();
});

onUnmounted(() => {
    stopImageSlider();
});
</script>

<style scoped>
.room-card {
    cursor: pointer;
}

.room-thumbnail {
    object-fit: cover;
    height: 100%;
    width: 100%;
}

.room-images-container {
    margin-bottom: 1rem;
}

.room-large-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    opacity: 1;
    transform: scale(1);
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
}

.room-large-image.fade-out {
    opacity: 0;
    transform: scale(0.95);
}

.room-thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.room-thumbnail-item {
    width: 80px;
    height: 60px;
    object-fit: cover;
    cursor: pointer;
    border: 1px solid #dee2e6;
    padding: 0.25rem;
    background-color: #fff;
    max-width: 100%;
    transition: opacity 0.2s ease-in-out, border-color 0.2s ease-in-out;
}

.room-thumbnail-item:hover {
    opacity: 0.8;
    border-color: #007bff;
}

.active-thumbnail {
    border-color: #007bff !important;
    opacity: 0.9;
}

.room-single-thumbnail {
    max-height: 500px;
    width: 100%;
    object-fit: cover;
}
</style>
