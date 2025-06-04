<template>
  <div id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
      <!-- Sidebar -->
      <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
          <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
          </div>
          <div class="sidebar-brand-text mx-3">Monata <sup>Admin</sup></div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item active">
          <router-link class="nav-link" to="/admin/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </router-link>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Nav Item - Tables -->
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/tables">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span>
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/rooms">
            <span>Rooms</span>
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/properties">
            <span>Properties</span>
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/room-types">
            <span>Room Types</span>
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/bookings">
            <span>Booking</span>
          </router-link>
        </li>

        <!-- Add more nav items here based on admin/index.html -->
        <li class="nav-item">
          <router-link class="nav-link" to="/admin/services">
            <i class="fas fa-fw fa-table"></i>
            <span>Service</span>
          </router-link>
        </li>

        <li class="nav-item">
          <router-link class="nav-link" to="/admin/contacts">
            <i class="fas fa-fw fa-table"></i>
            <span>Contact</span>
          </router-link>
        </li>
      </ul>
      <!-- End of Sidebar -->

      <!-- Content Wrapper -->
      <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
          <!-- Topbar -->
          <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" @click="toggleSidebar">
              <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Search -->
            <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
              <div class="input-group">
                <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                  aria-label="Search" aria-describedby="basic-addon2">
                <div class="input-group-append">
                  <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                  </button>
                </div>
              </div>
            </form>
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
              <!-- Nav Item - User Information -->
              <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
                  <img class="img-profile rounded-circle" src="@/modules/admin/assets/img/undraw_profile.svg">
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
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
          <!-- End of Topbar -->

          <!-- Begin Page Content -->
          <div class="container-fluid">
            <router-view></router-view> <!-- Page content will be rendered here -->
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

      </div>
      <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
    </a>

  </div>
</template>

<script lang="ts">
import { ref, defineComponent, onMounted } from 'vue';

export default defineComponent({
  name: 'AdminLayout',
  setup() {
    const isSidebarToggled = ref(false);

    const toggleSidebar = () => {
      isSidebarToggled.value = !isSidebarToggled.value;
      const wrapper = document.getElementById('wrapper');
      if (wrapper) {
        wrapper.classList.toggle('toggled', isSidebarToggled.value);
      }
    };

    onMounted(() => {
      const script = document.createElement('script');
      script.async = true;
      document.body.appendChild(script);
    });

    return {
      toggleSidebar
    };
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
  /* Nếu muốn sidebar có scroll riêng khi quá dài: */
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
  /* Nếu muốn phần nội dung cuộn khi quá dài: */
  overflow-y: auto;
}

#wrapper.toggled #accordionSidebar {
  display: none !important;
}
</style>
