import type { RouteRecordRaw } from 'vue-router'
import Dashboard from './views/Dashboard.vue'
import Tables from './views/Tables.vue'
import Properties from './views/Properties.vue'
import AdminLayout from './components/layouts/AdminLayout.vue'
import Bookings from './views/Bookings.vue'
import RoomType from './views/RoomType.vue'
import Rooms from './views/Room.vue'

import ServiceList from '@/modules/admin/views/service/ServiceList.vue'
import ServiceCreate from '@/modules/admin/views/service/ServiceCreate.vue'
import ContactList from '@/modules/admin/views/contact/ContactList.vue'
import UserList from '@/modules/admin/views/user/UserList.vue'
import UserCreate from '@/modules/admin/views/user/UserCreate.vue'

const adminRoutes: Array<RouteRecordRaw> = [
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: Dashboard,
        meta: { requiresAuth: true, requiresAdmin: true },
      },
      {
        path: 'tables',
        name: 'AdminTables',
        component: Tables,
        meta: { requiresAuth: true, requiresAdmin: true },
      },
      {
        path: 'properties',
        name: 'AdminProperties',
        component: Properties,
        meta: { requiresAdmin: true, requiresSuperAdmin: true },
      },
      {
        path: 'bookings',
        name: 'AdminBookings',
        component: Bookings,
        meta: { requiresAdmin: true },
      },
      {
        path: 'room-types',
        name: 'AdminRoomTypes',
        component: RoomType,
        meta: { requiresAdmin: true, requiresSuperAdmin: true },
      },
      {
        path: '', // Default child route for /admin
        redirect: '/admin/dashboard',
      },
      {
        path: 'services',
        name: 'AdminServiceList',
        component: ServiceList,
        meta: { requiresAuth: true, requiresAdmin: true, requiresSuperAdmin: true },
      },
      {
        path: 'contacts',
        name: 'AdminContactList',
        component: ContactList,
        meta: { requiresAuth: true, requiresAdmin: true },
      },
      {
        path: 'rooms',
        name: 'Rooms',
        component: Rooms,
        meta: { requiresAuth: true, requiresAdmin: true, requiresSuperAdmin: true },
      },
      {
        path: 'users',
        name: 'AdminUserList',
        component: UserList,
        meta: { requiresAuth: true, requiresAdmin: true, requiresSuperAdmin: true },
      },
      {
        path: 'users/create',
        name: 'AdminUserCreate',
        component: UserCreate,
        meta: { requiresAuth: true, requiresAdmin: true, requiresSuperAdmin: true },
      },
    ],
  },
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('@/modules/admin/views/Auth/Login.vue'),
  },
]

export default adminRoutes
