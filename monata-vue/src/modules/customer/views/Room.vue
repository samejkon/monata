<script setup>
import Header from '@/modules/customer/components/layouts/Header.vue';
import Footer from '@/modules/customer/components/layouts/Footer.vue';
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '@/modules/customer/lib/axios';

const route = useRoute();
const rooms = ref([]);
const isLoading = ref(false);
const error = ref(null);

const fetchRooms = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const { checkin_at, checkout_at, roomType } = route.query;

    const params = {};
    if (checkin_at) params.checkin_at = checkin_at;
    if (checkout_at) params.checkout_at = checkout_at;
    if (roomType) params.room_type_id = roomType;

    const response = await api.get('/rooms', { params });
    rooms.value = response.data.data;
  } catch (err) {
    console.error('Error fetching rooms:', err);
    error.value = 'Không thể tải danh sách phòng. Vui lòng thử lại sau!';
    rooms.value = [];
  } finally {
    isLoading.value = false;
  }
};

watch(
  () => route.query,
  () => {
    fetchRooms();
  },
  { immediate: true }
);
const heroImage = new URL('@/modules/customer/assets/img/slide/room.png', import.meta.url).href
</script>

<template>
  <div class="bg-dark">
    <Header />
  </div>
  <div class="hero" :style="{ backgroundImage: 'url(' + heroImage + ')' }"></div>
  <main class="">
    <div class="container my-3">
      <div class="row">
        <div v-for="room in rooms" @click="$router.push({ name: 'roomDetail', params: { id: room.id } })"
          class="col-md-4 mb-5">
          <div class="p-0">
            <img :src="room.thumbnail_path" alt="" class="rom-thumbnail">
            <div class="p-1">
              <p class="mb-1">Room {{ room.name }} - {{ room.room_type }} </p>
              <ul class="list mb-0">
                <li v-for="property in room.properties" class="mb-1">{{ property.value }} {{ property.name }}</li>
              </ul>
            </div>
            <button class="btn btn-blue w-100">Book Now</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>

<style scoped>
.hero {
  min-height: 500px;
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
}

.rom-thumbnail {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: cover;
}

.btn-blue {
  border-radius: 0;
  background-color: #007bff;
  color: white;
}

.btn-blue:hover {
  background-color: #ffffff;
  color: #007bff;
  border: solid 1px #007bff;
}

.border-none {
  border: none;
}
</style>
