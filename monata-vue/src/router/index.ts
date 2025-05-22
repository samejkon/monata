import { createRouter, createWebHistory } from 'vue-router'
import adminRouter from '../modules/admin/router'
import customerRouter from '../modules/customer/router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    ...adminRouter,
    ...customerRouter,
  ],
})

export default router
