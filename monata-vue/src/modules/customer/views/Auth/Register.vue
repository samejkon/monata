<script setup lang="ts">
import { reactive, ref } from 'vue'
import { api, csrf } from '@/modules/customer/lib/axios'
import type { RegisterForm, ValidationErrors } from '@/modules/customer/types/auth'
import { useAuthStore } from '@/modules/customer/stores/auth'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const toast = useToast()
const router = useRouter()
const authStore = useAuthStore()
const errors = ref<ValidationErrors>({})
const registerForm = reactive<RegisterForm>({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

async function register() {
  errors.value = {}

  try {
    await csrf.get('/sanctum/csrf-cookie')
    await api.post(`/register`, registerForm)
    authStore.login()

    toast.success("Register succsessfully!")
    router.push('/')
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      toast.error("Register fail!")
    }
  }
}
</script>

<template>
  <main>
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header text-center">
              <h3>Login</h3>
            </div>
            <div class="card-body">
              <form @submit.prevent="register()">
                <div class="mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input type="text" class="form-control" id="name" placeholder="Enter your name"
                    v-model="registerForm.name" />
                  <div v-if="errors.name" class="text-danger small">
                    {{ errors.name[0] }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <input type="text" class="form-control" id="email" placeholder="Enter your email"
                    v-model="registerForm.email" />
                  <div v-if="errors.email" class="text-danger small">
                    {{ errors.email[0] }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" placeholder="Enter your password"
                    v-model="registerForm.password" />
                  <div v-if="errors.password" class="text-danger small">
                    {{ errors.password[0] }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="password_confirmation"
                    placeholder="Confirm your password" v-model="registerForm.password_confirmation" />
                  <div v-if="errors.password" class="text-danger small">
                    {{ errors.password[0] }}
                  </div>
                </div>
                <div class="mb-3 form-check">
                  <label class="form-check-label" for="rememberMe">Do you have an account?</label>
                  <router-link to="/login" class="form-check-label"> Login</router-link>
                </div>
                <button type="submit" class="mb-3 btn btn-primary w-100">Register</button>
                <router-link to="/" class="btn btn-danger w-100">Back</router-link>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
