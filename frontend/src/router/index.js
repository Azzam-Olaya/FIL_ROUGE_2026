import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Welcome from '../views/Welcome.vue';
import Login from '../views/Auth/Login.vue';
import Register from '../views/Auth/Register.vue';
import VerifyIdentity from '../views/Auth/VerifyIdentity.vue';
import AdminDashboard from '../views/Admin/Dashboard.vue';
import ClientDashboard from '../views/Client/Dashboard.vue';
import FreelancerDashboard from '../views/Freelancer/Dashboard.vue';

import Messaging from '../views/Messaging.vue';

const routes = [
    { path: '/', name: 'Welcome', component: Welcome },
    { path: '/login', name: 'Login', component: Login },
    { path: '/register', name: 'Register', component: Register },
    { path: '/verify-identity', name: 'VerifyIdentity', component: VerifyIdentity },
    { path: '/messaging', name: 'Messaging', component: Messaging, meta: { requiresAuth: true } },
    { path: '/admin/dashboard', name: 'AdminDashboard', component: AdminDashboard, meta: { requiresAuth: true, role: 'admin' } },
    { path: '/client/dashboard', name: 'ClientDashboard', component: ClientDashboard, meta: { requiresAuth: true, role: 'client' } },
    { path: '/freelancer/dashboard', name: 'FreelancerDashboard', component: FreelancerDashboard, meta: { requiresAuth: true, role: 'freelancer' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    // Check if the route requires authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next('/login');
    }

    // Check if the route has role restrictions
    if (to.meta.role && authStore.userRole !== to.meta.role) {
        // Redirect to their own dashboard if they try to access another role's dashboard
        if (authStore.userRole === 'admin') return next('/admin/dashboard');
        if (authStore.userRole === 'client') return next('/client/dashboard');
        if (authStore.userRole === 'freelancer') return next('/freelancer/dashboard');
        return next('/');
    }

    next();
});

export default router;
