import type { RouteRecordRaw } from 'vue-router';
import AdminLayout from '@/modules/admin/components/layouts/AdminLayout.vue';
import Dashboard from '@/modules/admin/views/Dashboard.vue';
import Tables from '@/modules/admin/views/Tables.vue';
import Properties from '@/modules/admin/views/Properties.vue';
import ServiceList from '@/modules/admin/views/service/ServiceList.vue';
import ServiceCreate from '@/modules/admin/views/service/ServiceCreate.vue';

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
            },
            {
                path: 'services',
                name: 'AdminServiceList',
                component: ServiceList,
                meta: { requiresAuth: true },
            },
            {
                path: 'services/create',
                name: 'AdminServiceCreate',
                component: ServiceCreate,
                meta: { requiresAuth: true }
            }
        ]
    }
];

export default adminRoutes; 
