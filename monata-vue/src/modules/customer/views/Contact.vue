<script setup lang="ts">
import '../assets/scss/main.scss'
import Header from '../components/layouts/Header.vue'
import Contact from '../components/pages/Contact.vue'
import Footer from '../components/layouts/Footer.vue'
import CheckAvailable from '../components/forms/CheckAvailable.vue'
</script>

<template>
  <Header bgClass="2" title="Get in Touch" description="" />

  <main>
    <Contact />
  </main>

  <Footer />

  <CheckAvailable />
</template>