<template>
  <div id="page-top">
    <div id="wrapper">
      <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
          <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
          </div>
          <div class="sidebar-brand-text mx-3">Monata <sup>Admin</sup></div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item" :class="{ 'active': isActiveRoute('/admin/dashboard') }">
          <router-link class="nav-link" to="/admin/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </router-link>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item" :class="{ 'active': isRoomsDropdownActive || isRoomsDropdownOpen }">
          <a class="nav-link collapsed" href="#" @click.prevent="toggleRoomsDropdown"
            :aria-expanded="isRoomsDropdownOpen">
            <i class="fas fa-fw fa-home"></i>
            <span>Rooms & Properties</span>
            <i class="fas fa-angle-down dropdown-arrow" :class="{ 'rotate-180': isRoomsDropdownOpen }"></i>
          </a>
          <div id="collapseRooms" :class="{ 'collapse': true, 'show': isRoomsDropdownOpen }"
            aria-labelledby="headingRooms" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Room Management:</h6>
              <router-link class="collapse-item" :class="{ 'active': isActiveRoute('/admin/rooms') }"
                to="/admin/rooms">Rooms</router-link>
              <router-link class="collapse-item" :class="{ 'active': isActiveRoute('/admin/properties') }"
                to="/admin/properties">Properties</router-link>
              <router-link class="collapse-item" :class="{ 'active': isActiveRoute('/admin/room-types') }"
                to="/admin/room-types">Room Types</router-link>
            </div>
          </div>
        </li>
        <li class="nav-item" :class="{ 'active': isActiveRoute('/admin/bookings') }">
          <router-link class="nav-link" to="/admin/bookings">
            <span>Booking</span>
          </router-link>
        </li>

        <li class="nav-item" :class="{ 'active': isActiveRoute('/admin/services') }">
          <router-link class="nav-link" to="/admin/services">
            <span>Service</span>
          </router-link>
        </li>

        <li class="nav-item" :class="{ 'active': isActiveRoute('/admin/contacts') }">
          <router-link class="nav-link" to="/admin/contacts">
            <span>Contact</span>
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/users">
            <i class="fas fa-fw fa-table"></i>
            <span>Users</span>
          </router-link>
        </li>
      </ul>
      <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
          <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" @click="toggleSidebar">
              <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">
              <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                  @click.prevent="toggleUserDropdown" :aria-expanded="isUserDropdownOpen">
                  <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
                  <img class="img-profile rounded-circle" src="@/modules/admin/assets/img/undraw_profile.svg">
                  <i class="fas fa-caret-down dropdown-arrow-user ml-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow"
                  :class="{ 'animated--grow-in': isUserDropdownOpen, 'show': isUserDropdownOpen }"
                  aria-labelledby="userDropdown">
                  <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                  </a>
                  <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                  </a>
                  <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                  </a>
                </div>
              </li>
            </ul>
          </nav>
          <div class="container-fluid">
            <router-view></router-view>
          </div>
        </div>
      </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
    </a>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute } from 'vue-router';  

const route = useRoute(); 

const isSidebarToggled = ref(false);
const isCollapseTwoOpen = ref(false);
const isUserDropdownOpen = ref(false);
const isRoomsDropdownOpen = ref(false);

const toggleSidebar = () => {
  isSidebarToggled.value = !isSidebarToggled.value;
  const wrapper = document.getElementById('wrapper');
  if (wrapper) {
    wrapper.classList.toggle('toggled', isSidebarToggled.value);
  }
};

const toggleCollapseTwo = () => {
  isCollapseTwoOpen.value = !isCollapseTwoOpen.value;
  isUserDropdownOpen.value = false;
  isRoomsDropdownOpen.value = false;
};

const toggleUserDropdown = () => {
  isUserDropdownOpen.value = !isUserDropdownOpen.value;
  isCollapseTwoOpen.value = false;
  isRoomsDropdownOpen.value = false;
};

const toggleRoomsDropdown = () => {
  isRoomsDropdownOpen.value = !isRoomsDropdownOpen.value;
  isCollapseTwoOpen.value = false;
  isUserDropdownOpen.value = false;
};

const isActiveRoute = (path: string): boolean => {
  return route.path === path;
};

const isComponentsDropdownActive = computed(() => {

  return false;
});

const isRoomsDropdownActive = computed(() => {
  return route.path.startsWith('/admin/rooms') ||
    route.path.startsWith('/admin/properties') ||
    route.path.startsWith('/admin/room-types');
});

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;

  const collapseTwoElement = document.getElementById('collapseTwo');
  const collapseTwoToggler = document.querySelector('.nav-item a[data-target="#collapseTwo"], .nav-item a[href="#"][aria-expanded][aria-controls="collapseTwo"]');

  if (collapseTwoElement && collapseTwoToggler) {
    if (!collapseTwoElement.contains(target) && !collapseTwoToggler.contains(target)) {
      isCollapseTwoOpen.value = false;
    }
  }

  const userDropdownElement = document.getElementById('userDropdown');
  const userDropdownMenu = document.querySelector('.dropdown-menu-right');

  if (userDropdownElement && userDropdownMenu) {
    if (!userDropdownMenu.contains(target) && !userDropdownElement.contains(target)) {
      isUserDropdownOpen.value = false;
    }
  }

  const roomsDropdownElement = document.getElementById('collapseRooms');
  const roomsDropdownToggler = document.querySelector('.nav-item a[data-target="#collapseRooms"], .nav-item a[href="#"][aria-expanded][aria-controls="collapseRooms"]');

  if (roomsDropdownElement && roomsDropdownToggler) {
    if (!roomsDropdownElement.contains(target) && !roomsDropdownToggler.contains(target)) {
      isRoomsDropdownOpen.value = false;
    }
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

import { watch } from 'vue';
watch(() => route.path, (newPath, oldPath) => {
  if (isRoomsDropdownActive.value) {
    isRoomsDropdownOpen.value = true;
  } else {
    isRoomsDropdownOpen.value = false;
  }
  if (isComponentsDropdownActive.value) {
    isCollapseTwoOpen.value = true;
  } else {
    isCollapseTwoOpen.value = false;
  }
});
</script>

<style>
@import '@/modules/admin/assets/css/sb-admin-2.min.css';

#wrapper {
  display: flex;
  min-height: 100vh;
}

#accordionSidebar {
  height: 100vh;
  position: sticky;
  top: 0;
  flex-shrink: 0;
  overflow-y: auto;
}

#content-wrapper {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

#content {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
}

.container-fluid {
  flex: 1 1 auto;
  overflow-y: auto;
}

#wrapper.toggled #accordionSidebar {
  display: none !important;
}

.navbar-nav .nav-item .nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.navbar-nav .nav-item .nav-link .dropdown-arrow {
  margin-left: auto;
  transition: transform 0.2s ease-in-out;
}

.navbar-nav .nav-item .nav-link .dropdown-arrow.rotate-180 {
  transform: rotate(180deg);
}

.navbar-nav .nav-item .nav-link .dropdown-arrow-user {
  margin-left: auto;
}

.dropdown-menu {
  display: none;
  position: absolute;
  z-index: 1000;
}

.dropdown-menu.show {
  display: block;
}

.collapse {
  display: none;
}

.collapse.show {
  display: block;
}

.sidebar .nav-item.active .nav-link {
  color: #fff;
  background-color: rgba(255, 255, 255, 0.2);
}

.sidebar .nav-item.active .nav-link i {
  color: #fff;
}

.sidebar .collapse-item.active {
  color: #4e73df;
  font-weight: bold;
}
</style>
