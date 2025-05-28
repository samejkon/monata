<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow rounded-4">
          <div class="card-body p-4">
            <h3 class="mb-4 text-center">Create Service</h3>
            <form class="row g-3" @submit.prevent="handleCreateSubmit">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" 
                       class="form-control"
                       v-model="form.name" 
                       placeholder="Enter service name...">
                <div v-if="errors.name" class="text-danger">{{ errors.name[0] }}</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Price</label>
                <input type="number" 
                       class="form-control"
                       v-model="form.price" 
                       placeholder="Enter price...">
                <div v-if="errors.price" class="text-danger">{{ errors.price[0] }}</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <div class="d-flex">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" 
                           type="radio" 
                           name="status" 
                           id="status_active" 
                           :value="ServiceStatus.Active"
                           v-model="form.status">
                    <label class="form-check-label" for="status_active">Active</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" 
                           type="radio" 
                           name="status" 
                           id="status_inactive" 
                           :value="ServiceStatus.Inactive"
                           v-model="form.status">
                    <label class="form-check-label" for="status_inactive">Inactive</label>
                  </div>
                </div>
                <div v-if="errors.status" class="text-danger">{{ errors.status[0] }}</div>
              </div>

              <div class="col-12 text-center mt-3">
                <button type="submit" class="btn btn-primary px-5 py-2">Create Service</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useService } from '../../composables/service/Service.service';
  import { ServiceStatus } from '../../stores/enum/Service';

  const { form, errors, handleCreateSubmit } = useService();
</script>
