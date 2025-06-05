<template>
  <div class="admin-login-container">
    <h2>Admin Login</h2>
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button type="submit">Login</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { api, csrf } from '@/modules/admin/lib/axios'

const email = ref('');
const password = ref('');
const router = useRouter();

const handleLogin = async () => {
  // Here you would typically send the login credentials to your backend
  // For now, let's just simulate a successful login and redirect
  // console.log('Admin login attempt:', email.value, password.value);
    try {
        await csrf.get('/sanctum/csrf-cookie'); // Fetch CSRF cookie
        const response = await api.post('/login', {
            email: email.value,
            password: password.value
        })
        if (response.status === 200) {
            router.push({ name: 'AdminDashboard' });
        }
    } catch (error: any) {
        console.error('Login failed:', error)
        // Display error message to the user
        if (error.response && error.response.status === 401) {
          alert('Invalid credentials'); // Or use a more sophisticated notification system
        } else {
          alert('An error occurred during login. Please try again.');
        }
    }
  // In a real application, you'd make an API call here
  // If login is successful, redirect to admin dashboard
  // router.push({ name: 'AdminDashboard' });
};
</script>

<style scoped>
.admin-login-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f0f2f5;
  font-family: Arial, sans-serif;
}

h2 {
  color: #333;
  margin-bottom: 20px;
}

form {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 300px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #555;
}

input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box; /* Ensures padding doesn't affect overall width */
}

button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

button:hover {
  background-color: #0056b3;
}
</style> 
