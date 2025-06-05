<script setup>
import Header from '@/modules/customer/components/layouts/Header.vue';
import Footer from '@/modules/customer/components/layouts/Footer.vue';
import { ref } from 'vue';
import { onMounted } from 'vue';
import { api } from '@/modules/customer/lib/axios';

const rooms = ref([]);

const fetchRooms = async () => {
  try {
    const response = await api.get('/rooms')
    return response.data.data
  } catch (error) {
    console.error('Error fetching rooms:', error)
    return []
  }
};

onMounted(async () => {
  rooms.value = await fetchRooms();
});
</script>

<template>
  <Header bgClass="4  " title="" description="" />

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
