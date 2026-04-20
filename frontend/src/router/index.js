import { createRouter, createWebHistory } from 'vue-router';
import Welcome from '../views/Welcome.vue';
import Login from '../views/Auth/Login.vue';
import Register from '../views/Auth/Register.vue';
import AdminDashboard from '../views/Admin/Dashboard.vue';
import ClientDashboard from '../views/Client/Dashboard.vue';
import FreelancerDashboard from '../views/Freelancer/Dashboard.vue';

const routes = [
    { path: '/', name: 'Welcome', component: Welcome },
    { path: '/login', name: 'Login', component: Login },
    { path: '/register', name: 'Register', component: Register },
    { path: '/admin', name: 'AdminDashboard', component: AdminDashboard, meta: { role: 'admin' } },
    { path: '/client', name: 'ClientDashboard', component: ClientDashboard, meta: { role: 'client' } },
    { path: '/freelancer', name: 'FreelancerDashboard', component: FreelancerDashboard, meta: { role: 'freelancer' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
