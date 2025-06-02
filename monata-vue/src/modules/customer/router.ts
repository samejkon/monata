// import Index from './views/Index.vue'
import Index from '../customer/views/Index.vue'

export default [
  {
    path: '/',
    name: 'home',
    component: () => import('./views/Index.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('./views/Auth/Login.vue'),
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('./views/Auth/Register.vue'),
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('./views/Profile.vue'),
  },
]
