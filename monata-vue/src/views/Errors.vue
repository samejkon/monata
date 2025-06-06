<template>
  <div class="d-flex flex-column align-items-center justify-content-center vh-100 bg-light text-dark">
    <h1 class="display-1 text-danger mb-3">403 Access Denied</h1>
    <p class="lead mb-4">You do not have permission to access this page.</p>
    <router-link to="/">Back to home</router-link>
  </div>
</template>

<script>
export default {
  name: 'Errors'
}
</script>