<script setup lang="ts">
import LoginForm from '@/modules/customer/components/forms/LoginForm.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

function onLoggedIn() {
  router.push('/')
}
</script>

<template>
  <main>
    <div class="container mt-5 text-dark">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header text-center">
              <h3>Login</h3>
            </div>
            <div class="card-body">
              <LoginForm @loggedIn="onLoggedIn" />
              <router-link to="/" class="btn btn-danger w-100 mt-3">Back to Home</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
