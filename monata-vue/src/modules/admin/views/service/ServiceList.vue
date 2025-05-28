<template>
    <div>
        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Service List</h1>
        
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Service List</h6>
                <router-link to="/admin/services/create" class="btn btn-primary"><SquarePlus/></router-link>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="service of services">
                                <td>{{ service.name }}</td>
                                <td>{{ service.price }}</td>
                                <td>
                                    <span :class="service.status === ServiceStatus.Active ? 'badge bg-success' : 'badge bg-danger'">
                                        {{ service.status === ServiceStatus.Active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="d-flex gap-2">
                                    <router-link :to="`/admin/services/${service.id}/edit`" class="btn btn-warning"><SquarePen /></router-link>
                                    <button class="btn btn-danger" @click="actionDeleteService(service.id)"><Trash2 /></button>
                                </td>
                            </tr>
                            <!-- Add more static rows here if needed -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Pagination v-model:currentPage="currentPage" :totalPages="totalPages"/>
    </div>
</template>

<script setup lang="ts">
  import { watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useService } from '../../composables/service/Service.service';
  import { ServiceStatus } from '../../stores/enum/Service';
  import Pagination from '../../components/layouts/Pagination.vue';
  import { SquarePen, Trash2, SquarePlus } from 'lucide-vue-next';

  const route = useRoute();
  const router = useRouter();

  const {
    services,
    totalPages,
    currentPage,
    fetchServices,
    actionDeleteService,
    } = useService();

  watch(currentPage, (newPage) => {
    router.push({ 
        query: { 
        ...route.query,
        page: newPage.toString(),
        },
    });
  });

  watch(() => route.query, () => {
    fetchServices();
  }, { immediate: true });
</script>

<style scoped>
/* Styles related to local DataTables vendor files have been removed. */
/* Basic table styling will come from sb-admin-2.min.css */
</style>