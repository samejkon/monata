<script setup lang="ts">
import { reactive } from 'vue'
import { api, csrf } from '@/modules/customer/lib/axios'
import { ref } from 'vue'
import type { LoginForm, ValidationErrors } from '@/modules/customer/types/auth'
import { useAuthStore } from '@/modules/customer/stores/auth'
import { useRouter } from 'vue-router'

const router = useRouter()
const authStore = useAuthStore()
const errors = ref<ValidationErrors>({})
const loginForm = reactive<LoginForm>({
  email: '',
  password: '',
})

async function login() {
  errors.value = {}

  try {
    await csrf.get('/sanctum/csrf-cookie')
    await api.post(`/login`, loginForm)
    authStore.login()

    router.push('/')
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Login failed:', error.message)
      alert('Login failed!')
    }
  }
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
              <form @submit.prevent="login()">
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <input type="text" class="form-control" id="email" placeholder="Enter your email"
                    v-model="loginForm.email" />
                  <div v-if="errors.email" class="text-danger small">
                    {{ errors.email[0] }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" placeholder="Enter your password"
                    v-model="loginForm.password" />
                  <div v-if="errors.password" class="text-danger small">
                    {{ errors.password[0] }}
                  </div>
                </div>
                <div class="mb-3 form-check">
                  <label class="form-check-label" for="rememberMe">Do you want to create an account?</label>
                  <router-link to="/register" class="form-check-label"> Register</router-link>
                </div>
                <button type="submit" class="mb-3 btn btn-primary w-100">Login</button>
                <router-link to="/" class="btn btn-danger w-100">Back</router-link>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
