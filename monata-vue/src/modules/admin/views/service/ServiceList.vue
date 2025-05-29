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
                <div class="row">
                    <div class="col-lg-2">
                        <div class="dataTables_length" id="dataTable_length">
                            <label>Show 
                                <select 
                                    v-model="searchForm.per_page" 
                                    @change="onSearch"
                                    name="dataTable_length" 
                                    aria-controls="dataTable" 
                                    class="custom-select custom-select-sm form-control form-control-sm"
                                >
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select> entries
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <form @submit.prevent="onSearch" class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <input v-model="searchForm.name" type="text" class="form-control form-control-sm" placeholder="Service name ..." />
                            </div>
                            <div class="col-md-4">
                                <input v-model="searchForm.price" type="number" class="form-control form-control-sm" placeholder="Service price ..." />
                            </div>
                            <div class="col-md-2">
                                <select v-model="searchForm.status" class="custom-select custom-select-sm form-control form-control-sm">
                                    <option value="">-- Select status --</option>
                                    <option :value="ServiceStatus.Active">Active</option>
                                    <option :value="ServiceStatus.Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
                            <tr v-for="service of services" :key="service.id">
                                <td>
                                    <template v-if="editingService?.id === service.id">
                                        <input 
                                            v-model="editingService.name" 
                                            type="text" 
                                            class="form-control"
                                        />
                                    </template>
                                    <template v-else>
                                        {{ service.name }}
                                    </template>
                                </td>
                                <td>
                                    <template v-if="editingService?.id === service.id">
                                        <input 
                                            v-model="editingService.price" 
                                            type="number" 
                                            class="form-control"
                                        />
                                    </template>
                                    <template v-else>
                                        {{ service.price }}
                                    </template>
                                </td>
                                <td>
                                    <template v-if="editingService?.id === service.id">
                                        <select v-model="editingService.status" class="form-control">
                                            <option :value="ServiceStatus.Active">Active</option>
                                            <option :value="ServiceStatus.Inactive">Inactive</option>
                                        </select>
                                    </template>
                                    <template v-else>
                                        <span :class="service.status === ServiceStatus.Active ? 'badge bg-success' : 'badge bg-danger'">
                                            {{ service.status === ServiceStatus.Active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </template>
                                </td>
                                <td class="d-flex gap-2">
                                    <template v-if="editingService?.id === service.id">
                                        <button class="btn btn-success" @click="handleUpdateService">
                                            <Check />
                                        </button>
                                        <button class="btn btn-secondary" @click="cancelEdit">
                                            <X />
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button class="btn btn-warning" @click="startEdit(service)">
                                            <SquarePen />
                                        </button>
                                        <button class="btn btn-danger" @click="actionDeleteService(service.id)">
                                            <Trash2 />
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Pagination 
            v-model:currentPage="currentPage" 
            :meta="meta"
            :currentPage="currentPage"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, watch ,onMounted} from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useService } from '../../composables/service/Service.service';
import { ServiceStatus } from '../../stores/enum/Service';
import Pagination from '../../components/layouts/Pagination.vue';
import { SquarePen, Trash2, SquarePlus, Check, X } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const {
    services,
    meta,
    currentPage,
    fetchServices,
    actionDeleteService,
    updateService,
    editingService,
    startEdit,
    cancelEdit,
    handleUpdateService,
    searchForm,
    onSearch,
    syncFormWithQuery,
} = useService();

watch(() => route.query, (newQuery) => {
    fetchServices();
    syncFormWithQuery();
}, { immediate: true, deep: true });

watch(currentPage, (newPage) => {
    router.push({ 
        query: { 
            ...route.query,
            page: newPage.toString(),
        },
    });
});

onMounted(() => {
    syncFormWithQuery();
});

</script>

<style scoped>
.form-control {
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.form-control:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

label {
    font-weight: normal;
    text-align: left;
    white-space: nowrap;
}
</style>
