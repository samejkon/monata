<script setup lang="ts">
import { useAuthStore } from '@/modules/customer/stores/auth'
import { api } from '@/modules/customer/lib/axios'
import { ref } from 'vue'
import CheckAvailable from '../forms/CheckAvailable.vue'
import { useToast } from 'vue-toastification'
import { useRoute, useRouter } from 'vue-router'
import { ShoppingBasket} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const props = defineProps({
  bgClass: {
    type: String,
    default: '1'
  },
  title: {
    type: String,
    default: 'Montana Resort'
  },
  description: {
    type: String,
    default: 'Unlock to enjoy the view of Martine'
  }
})

const authStore = useAuthStore()
const showModal = ref(false)

const openModal = () => {
  showModal.value = true
}

async function logout() {
  try {
    await api.post(`/logout`)
    authStore.logout()
    toast.success("You had logout!")
    router.push('/')
  } catch (error: any) {
    console.error('Logout failed:', error.message)
    alert('Logout failed!')
  }
}
</script>

<template>
  <header>
    <section :class="['section-header', `section-header_${bgClass}`]">
      <div class="menu-toogle-display d-none" id="menu-toogle">
        <i class="icon fa fa-menu" id="close-menu-icon" onclick="toggleMenu()"></i>
        <ul class="menu-toogle-list">
          <li class="menu-toogle-item"><router-link to="/">Home</router-link></li>
          <li class="menu-toogle-item"><router-link to="/rooms">Rooms</router-link></li>
          <li class="menu-toogle-item">About</li>
          <li class="menu-toogle-item">Contact</li>
          <li class="menu-toogle-item">
            <a href="#" class="menu-toogle-item-link text-decor-none" @click.prevent="openModal">Book A Room</a>
          </li>
        </ul>
      </div>
      <nav class="nav-bar">
        <div class="nav-start">
          <a href="#" class="nav-item text-decor-none" :class="{ 'active-under': route.name === 'home' }">
            <router-link to="/" class="text-light">Home</router-link>
          </a>
          <a href="#" class="nav-item text-decor-none" :class="{ 'active-under': route.name === 'rooms' }">
            <router-link to="/rooms" class="text-light">Rooms</router-link>
          </a>
          <a href="#" class="nav-item text-decor-none">About</a>
          <a href="#" class="nav-item text-decor-none">Contact</a>
        </div>
        <div class="logo">
          <img src="@/modules/customer/assets/img/logo.png" alt="motana logo" />
        </div>
        <div class="menu-icon-display" id="menu-icon" onclick="toggleMenu()">
          <img src="../assets/icon/buttonmenu.svg" alt="menu icon" />
        </div>
        <div class="nav-end">
          <router-link to="/login" class="nav-item text-decor-none text-light" v-if="!authStore.authenticated">
            Sign in to your account
          </router-link>
          <router-link to="/profile" class="text-light" v-if="authStore.authenticated">
            Profile /
          </router-link>
          <a href="#" @click.prevent="logout()" class="text-light" v-if="authStore.authenticated">
            &nbsp; Logout
          </a>
          <button class="nav-booking" @click="openModal">
            Book A Room
          </button>
          <router-link to="/booking" class="text-light" v-if="authStore.authenticated">
            <ShoppingBasket />
          </router-link>
        </div>
      </nav>
      <div class="text-overlay">
        <div>
          <h3 class="text-overlay-heading">{{ title }}</h3>
          <p class="text-overlay-p">{{ description }}</p>
        </div>
      </div>
    </section>
    <CheckAvailable v-model="showModal" />
  </header>
</template>

<style scoped>
.nav-booking {
  cursor: pointer;
}
</style>
