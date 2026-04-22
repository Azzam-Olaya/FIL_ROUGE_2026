import { createRouter, createWebHistory } from 'vue-router';
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
    { path: '/messaging', name: 'Messaging', component: Messaging },
    { path: '/admin/dashboard', name: 'AdminDashboard', component: AdminDashboard, meta: { role: 'admin' } },
    { path: '/client/dashboard', name: 'ClientDashboard', component: ClientDashboard, meta: { role: 'client' } },
    { path: '/freelancer/dashboard', name: 'FreelancerDashboard', component: FreelancerDashboard, meta: { role: 'freelancer' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
