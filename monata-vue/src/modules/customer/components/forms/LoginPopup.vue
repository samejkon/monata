<script setup lang="ts">
import { reactive, computed } from 'vue'
import { api, csrf } from '@/modules/customer/lib/axios'
import { ref } from 'vue'
import type { LoginForm, ValidationErrors } from '@/modules/customer/types/auth'
import { useAuthStore } from '@/modules/customer/stores/auth'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const toast = useToast()
const router = useRouter()
const authStore = useAuthStore()
const errors = ref<ValidationErrors>({})
const loginForm = reactive<LoginForm>({
  email: '',
  password: '',
})

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'loginSuccess'): void
}>()

const isModalOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const closeModal = () => {
  isModalOpen.value = false
}

async function login() {
  errors.value = {}

  try {
    await csrf.get('/sanctum/csrf-cookie')
    await api.post(`/login`, loginForm)
    authStore.login()

    toast.success("Login successful!")
    emit('loginSuccess')
    closeModal()
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else if (error.response?.status === 401) {
      toast.error('Email or password is incorrect.')
    } else {
      console.error('Login failed:', error.message)
      toast.error("Login failed!")
    }
  }
}
</script>

<template>
  <div v-if="isModalOpen" class="login-modal-overlay" @click="closeModal">
    <div class="login-modal-content" @click.stop>
      <button type="button" class="btn-close-modal" @click="closeModal">
        <i class="fas fa-times"></i>
      </button>

      <div class="login-container">
        <div class="login-header">
          <h3>Login</h3>
          <p class="text-muted">Please log in to continue booking.</p>
        </div>

        <form @submit.prevent="login()" class="login-form">
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-envelope"></i>
              </span>
              <input 
                type="email" 
                class="form-control" 
                id="email" 
                placeholder="Enter your email"
                v-model="loginForm.email"
                :class="{ 'is-invalid': errors.email }"
              />
            </div>
            <div v-if="errors.email" class="invalid-feedback">
              {{ errors.email[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-lock"></i>
              </span>
              <input 
                type="password" 
                class="form-control" 
                id="password" 
                placeholder="Enter your password"
                v-model="loginForm.password"
                :class="{ 'is-invalid': errors.password }"
              />
            </div>
            <div v-if="errors.password" class="invalid-feedback">
              {{ errors.password[0] }}
            </div>
          </div>

          <div class="form-group text-center">
            <p class="mb-2">
                Don't have an account?
              <router-link to="/register" class="text-primary" @click="closeModal">
                Register
              </router-link>
            </p>
          </div>

          <button type="submit" class="btn btn-primary w-100 login-btn">
            Login
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1100;
}

.login-modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  position: relative;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-close-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #666;
  cursor: pointer;
  padding: 0.5rem;
  line-height: 1;
  z-index: 1;
  transition: color 0.2s ease;
}

.btn-close-modal:hover {
  color: #333;
}

.login-container {
  padding: 2rem;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h3 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.login-header p {
  font-size: 0.9rem;
  color: #666;
}

.login-form {
  margin-top: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  font-weight: 500;
  color: #333;
  margin-bottom: 0.5rem;
}

.input-group {
  border-radius: 8px;
  overflow: hidden;
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  border-right: none;
  color: #666;
}

.form-control {
  border: 1px solid #ced4da;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  height: auto;
}

.form-control:focus {
  box-shadow: none;
  border-color: #80bdff;
}

.login-btn {
  padding: 0.75rem;
  font-size: 1rem;
  font-weight: 500;
  margin-top: 1rem;
  background-color: #0d6efd;
  border: none;
  transition: background-color 0.2s ease;
}

.login-btn:hover {
  background-color: #0b5ed7;
}

.invalid-feedback {
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.text-primary {
  color: #0d6efd !important;
  text-decoration: none;
  font-weight: 500;
}

.text-primary:hover {
  text-decoration: underline;
}
</style>
