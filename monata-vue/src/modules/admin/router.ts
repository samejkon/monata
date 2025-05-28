import type { RouteRecordRaw } from 'vue-router';
import AdminLayout from '@/modules/admin/components/layouts/AdminLayout.vue';
import DashboardView from '@/modules/admin/views/Dashboard.vue';
// Import TablesView once it's created
import TablesView from '@/modules/admin/views/Tables.vue';

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
                path: '', // Default child route for /admin
                redirect: '/admin/dashboard'
            }
        ]
    }
];

export default adminRoutes; 