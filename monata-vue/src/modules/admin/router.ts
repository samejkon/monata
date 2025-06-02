import type { RouteRecordRaw } from 'vue-router'
import Dashboard from './views/Dashboard.vue'
import Tables from './views/Tables.vue'
import Properties from './views/Properties.vue'
import AdminLayout from './components/layouts/AdminLayout.vue'
import Bookings from './views/Bookings.vue'
import RoomType from './views/RoomType.vue'

import ServiceList from '@/modules/admin/views/service/ServiceList.vue'
import ServiceCreate from '@/modules/admin/views/service/ServiceCreate.vue'
import ContactList from '@/modules/admin/views/contact/ContactList.vue'

const adminRoutes: Array<RouteRecordRaw> = [
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: Dashboard,
        meta: { requiresAuth: true },
      },
      {
        path: 'tables',
        name: 'AdminTables',
        component: Tables,
        meta: { requiresAuth: true },
      },
      {
        path: 'properties',
        name: 'AdminProperties',
        component: Properties,
      },
      {
        path: 'bookings',
        name: 'AdminBookings',
        component: Bookings,
      },
      {
        path: 'room-types',
        name: 'AdminRoomTypes',
        component: RoomType,
      },
      {
        path: '', // Default child route for /admin
        redirect: '/admin/dashboard',
      },
      {
        path: 'services',
        name: 'AdminServiceList',
        component: ServiceList,
        meta: { requiresAuth: true },
      },
      {
        path: 'contacts',
        name: 'AdminContactList',
        component: ContactList,
        meta: { requiresAuth: true },
      },
    ],
  },
]

export default adminRoutes
