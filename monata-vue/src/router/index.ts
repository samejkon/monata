import { createRouter, createWebHistory } from 'vue-router'
import customerRouter from '../modules/customer/router'
import adminRoutes from '@/modules/admin/router';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...customerRouter,
    ...adminRoutes,
  ],
})

export default router
