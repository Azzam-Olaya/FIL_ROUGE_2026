import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import router from '../router';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor to add the bearer token
api.interceptors.request.use(
    (config) => {
        const authStore = useAuthStore();
        if (authStore.token) {
            config.headers.Authorization = `Bearer ${authStore.token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor to handle token expiration (401 errors)
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401 && !error.config.url.endsWith('/login')) {
            const authStore = useAuthStore();
            authStore.clearAuth();
            router.push('/login');
        }
        return Promise.reject(error);
    }
);

export default api;
