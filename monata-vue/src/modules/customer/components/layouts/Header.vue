<script setup lang="ts">
import { useAuthStore } from '@/modules/customer/stores/auth'
import { api } from '@/modules/customer/lib/axios'

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

async function logout() {
  try {
    await api.post(`/logout`)
    authStore.logout()
    alert('Logout successful!')
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
          <li class="menu-toogle-item">Home</li>
          <li class="menu-toogle-item">Rooms</li>
          <li class="menu-toogle-item">About</li>
          <li class="menu-toogle-item">Blog</li>
          <li class="menu-toogle-item">Pages</li>
          <li class="menu-toogle-item">Contact</li>
          <li class="menu-toogle-item">
            <a href="#" class="menu-toogle-item-link text-decor-none" onclick="toggleModal()">Book A Room</a>
          </li>
        </ul>
      </div>
      <nav class="nav-bar">
        <div class="nav-start">
          <a href="#" class="nav-item text-decor-none active-under">Home</a>
          <a href="#" class="nav-item text-decor-none">Rooms</a>
          <a href="#" class="nav-item text-decor-none">About</a>
          <a href="#" class="nav-item text-decor-none">Blog</a>
          <a href="#" class="nav-item text-decor-none">Pages</a>
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
          <button class="nav-booking" onclick="toggleModal()">
            Book A Room
          </button>
        </div>
      </nav>
      <div class="text-overlay">
        <div>
          <h3 class="text-overlay-heading">{{ title }}</h3>
          <p class="text-overlay-p">{{ description }}</p>
        </div>
      </div>
    </section>
  </header>
</template>
