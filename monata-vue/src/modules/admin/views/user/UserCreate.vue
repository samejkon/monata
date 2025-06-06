<template>
<div class="container mt-5">
    <div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <h3 class="mb-4 text-center">Create User</h3>
            <form class="row g-3" @submit.prevent="handleCreate">
            <div class="col-md-6">
                <label class="form-label d-flex">Name</label>
                <input type="text" class="form-control "
                        name="name" v-model="formCreate.name" placeholder="Enter name...">
                <div v-if="errors.name" class="text-danger d-flex">{{ errors.name[0] }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label d-flex">Email</label>
                <input type="email" class="form-control "
                        name="email" v-model="formCreate.email" placeholder="Enter email...">
                <div v-if="errors.email" class="text-danger d-flex">{{ errors.email[0] }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label d-flex">Phone</label>
                <input type="text" class="form-control " inputmode="numeric"
                        name="phone" v-model="formCreate.phone" placeholder="Enter phone number...">
                <div v-if="errors.phone" class="text-danger d-flex">{{ errors.phone[0] }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label d-block d-flex">Status</label>
                <div class="d-flex">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_active" :value="UserStatus.Active"
                            v-model="formCreate.status" >
                    <label class="form-check-label" for="status_active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_blocked" :value="UserStatus.Blocked"
                            v-model="formCreate.status" >
                    <label class="form-check-label" for="status_blocked">Blocked</label>
                </div>
                </div>
            </div>

            <div class="col-md-6">
            <label class="form-label d-flex">Password</label>
            <input v-model="formCreate.password" type="password" class="form-control" placeholder="Enter password..." />
            <div v-if="errors.password" class="text-danger d-flex">{{ errors.password[0] }}</div>
            </div>

            <div class="col-md-6">
            <label class="form-label d-flex">Confirm Password</label>
            <input v-model="formCreate.password_confirmation" type="password" class="form-control" placeholder="Confirm password..." />
            <div v-if="errors.password_confirmation" class="text-danger d-flex">{{ errors.password_confirmation[0] }}</div>
            </div>

            <div class="col-12 text-center mt-3">
                <button type="submit" class="btn btn-primary px-5 py-2">Create</button>
            </div>
            </form>
        </div>
        </div>
    </div>
    </div>
</div>
</template>
  
<script setup lang="ts">
import { UserStatus } from '../../stores/enum/User';
import {useUser} from "../../composables/user/User.service";

const {
    formCreate,
    errors,
    handleCreate,
} = useUser();
</script>

<style scoped>

</style>
