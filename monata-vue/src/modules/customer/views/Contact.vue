<script setup lang="ts">
import '../assets/scss/main.scss'
import Header from '../components/layouts/Header.vue'
import Contact from '../components/pages/Contact.vue'
import Footer from '../components/layouts/Footer.vue'

const heroImage = new URL('@/modules/customer/assets/img/slide/contact.png', import.meta.url).href

</script>

<template>
  <Header />
  <div class="hero" :style="{ backgroundImage: `url(${heroImage})` }">

  </div>
  <main>
    <Contact />
  </main>

  <Footer />
</template>

<style scoped>
.hero {
  background-size: cover;
  background-position: center;
  height: 50vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  text-align: center;
}

.hero-content {
  z-index: 5;
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 15px;
}

.hero-content p {
  font-size: 1.5rem;
}

/* Các kiểu dáng khác */
.nav-booking {
  cursor: pointer;
}
</style>
