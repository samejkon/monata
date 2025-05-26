import type { RouteRecordRaw } from 'vue-router';
import Dashboard from './views/Dashboard.vue';
import Tables from './views/Tables.vue';
import Properties from './views/Properties.vue';
import AdminLayout from './components/layouts/AdminLayout.vue';
import Bookings from './views/Bookings.vue';


const adminRoutes: Array<RouteRecordRaw> = [
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            {
                path: 'dashboard', name: 'AdminDashboard', component: Dashboard, meta: { requiresAuth: true }
            },
            {
                path: 'tables', name: 'AdminTables', component: Tables, meta: { requiresAuth: true }
            },
            {
                path: 'properties', name: 'AdminProperties', component: Properties
            },
            {
                path: 'bookings', name: 'AdminBookings', component: Bookings
            },
            {
                path: '', redirect: '/admin/dashboard'
            }
        ]
    }
];

export default adminRoutes; 