<script setup lang="ts">
import RegisterForm from '@/modules/customer/components/forms/RegisterForm.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

function onRegistered() {
  router.push('/')
}

function onSwitchToLogin() {
  router.push('/login')
}
</script>

<template>
  <main>
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header text-center">
              <h3>Register</h3>
            </div>
            <div class="card-body">
              <RegisterForm @registered="onRegistered" @switchToLogin="onSwitchToLogin" />
              <router-link to="/" class="btn btn-danger w-100 mt-3">Back to Home</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
