<script setup>
import Footer from '@/modules/customer/components/layouts/Footer.vue'
import Header from '@/modules/customer/components/layouts/Header.vue'
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/modules/customer/lib/axios' // Reusing admin axios instance for now, consider creating a customer-specific one if needed

const route = useRoute()
const roomDetails = ref(null)
const currentLargeImage = ref(null)
const currentImageIndex = ref(0)
const isTransitioning = ref(false)
let intervalId = null


const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

const fetchRoomDetails = async (roomId) => {
  try {
    const response = await api.get(`/rooms/${roomId}`)
    const fetchedData = response.data.data

    if (fetchedData) {
      let displayImages = []

      if (fetchedData.thumbnail_path) {
        displayImages.push({
          id: 'thumbnail_0',
          image_path: fetchedData.thumbnail_path
        })
      }

      if (fetchedData.images && fetchedData.images.length > 0) {
        fetchedData.images.forEach(img => {
          if (!fetchedData.thumbnail_path || img.image_path !== fetchedData.thumbnail_path) {
            displayImages.push(img)
          }
        })
      }

      roomDetails.value = {
        ...fetchedData,
        images: displayImages
      }

      if (roomDetails.value?.images?.length > 0) {
        currentLargeImage.value = roomDetails.value.images[0].image_path
        currentImageIndex.value = 0
        startImageSlider()
      } else {
        currentLargeImage.value = null
        stopImageSlider()
      }
    } else {
      roomDetails.value = null
      currentLargeImage.value = null
      stopImageSlider()
    }
  } catch (error) {
    console.error(`Error fetching details for room ${roomId}:`, error)
    roomDetails.value = null
    currentLargeImage.value = null
    stopImageSlider()
  }
}

const changeLargeImage = (imagePath) => {
  if (isTransitioning.value || currentLargeImage.value === imagePath) {
    return
  }

  isTransitioning.value = true
  const largeImageElement = document.querySelector('.room-large-image')

  if (largeImageElement) {
    largeImageElement.classList.add('fade-out')

    setTimeout(() => {
      currentLargeImage.value = imagePath
      currentImageIndex.value = roomDetails.value.images.findIndex((img) => img.image_path === imagePath)
      largeImageElement.classList.remove('fade-out')
      isTransitioning.value = false
    }, 300)
  } else {
    currentLargeImage.value = imagePath
    currentImageIndex.value = roomDetails.value.images.findIndex((img) => img.image_path === imagePath)
    isTransitioning.value = false
  }
}

const nextImage = () => {
  if (roomDetails.value?.images?.length > 1 && !isTransitioning.value) {
    isTransitioning.value = true
    const largeImageElement = document.querySelector('.room-large-image')
    if (largeImageElement) {
      largeImageElement.classList.add('fade-out')
      setTimeout(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % roomDetails.value.images.length
        currentLargeImage.value = roomDetails.value.images[currentImageIndex.value].image_path
        largeImageElement.classList.remove('fade-out')
        isTransitioning.value = false
      }, 300)
    } else {
      currentImageIndex.value = (currentImageIndex.value + 1) % roomDetails.value.images.length
      currentLargeImage.value = roomDetails.value.images[currentImageIndex.value].image_path
      isTransitioning.value = false
    }
  }
}

const startImageSlider = () => {
  if (roomDetails.value?.images?.length > 1 && !intervalId) {
    intervalId = setInterval(nextImage, 5000)
  }
}

const stopImageSlider = () => {
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

onMounted(async () => {
  const roomId = route.params.id
  if (roomId) {
    await fetchRoomDetails(roomId)
  }
})

onUnmounted(() => {
  stopImageSlider()
})
</script>

<template>
  <Header bgClass="4" title="" description="" />

  <main class="container mt-5 mb-5">
    <div v-if="roomDetails" class="p-4">
      <div class="row">
        <div class="col-lg-8">
          <h3 class=" text-center">{{ roomDetails.name }} <span class="text-muted">- {{ roomDetails.room_type }}</span>
          </h3>

          <!-- Image Gallery -->
          <div v-if="roomDetails.images && roomDetails.images.length > 0" class="room-images-container mb-4">
            <img :src="currentLargeImage" :alt="roomDetails.name"
              class="room-large-image img-fluid rounded shadow-sm mb-2">
            <div class="room-thumbnails">
              <img v-for="(image, index) in roomDetails.images" :key="image.id" :src="image.image_path"
                :alt="'Thumbnail ' + (index + 1)" class="room-thumbnail-item img-thumbnail rounded shadow-sm"
                @click="changeLargeImage(image.image_path)"
                :class="{ 'active-thumbnail': currentLargeImage === image.image_path }">
            </div>
          </div>
          <img v-else-if="roomDetails.thumbnail_path" :src="roomDetails.thumbnail_path" :alt="roomDetails.name"
            class="img-fluid mb-3 rounded shadow-sm room-single-thumbnail">

          <p v-if="roomDetails.description" class="lead">{{ roomDetails.description }}</p>
        </div>
        <div class="col-lg-4">
          <div class="pt-5">
            <h4 class="card-text"><strong>{{ formatCurrency(roomDetails.price) }} Per night</strong></h4>
            <ul class="list-group list-group-flush">
              <li v-for="prop in roomDetails.properties" :key="prop.property_id"
                class="list-group-item d-flex justify-content-between align-items-center">
                {{ prop.name }}
                <span class="badge bg-primary rounded-pill">{{ prop.value }}</span>
              </li>
            </ul>
          </div>
          <div class="">
            <button class="btn btn-primary w-100">Book Now</button>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="text-center py-5">
      <p class="lead">Loading room details or room not found...</p>
    </div>
  </main>

  <Footer />
</template>

<style scoped>
.room-images-container {
  margin-bottom: 1rem;
}

.room-large-image {
  width: 100%;
  height: 400px;
  /* Adjust height as needed */
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
  justify-content: start;
  /* Center thumbnails */
}

.room-thumbnail-item {
  width: 100px;
  /* Adjust size as needed */
  height: 75px;
  /* Adjust size as needed */
  object-fit: cover;
  cursor: pointer;
  border: 2px solid #dee2e6;
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .room-large-image {
    height: 250px;
  }

  .room-thumbnail-item {
    width: 80px;
    height: 60px;
  }
}
</style>
