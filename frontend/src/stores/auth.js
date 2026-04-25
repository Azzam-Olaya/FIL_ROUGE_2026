import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));
    const token = ref(localStorage.getItem('token') || null);

    const isAuthenticated = computed(() => !!token.value);
    const userRole = computed(() => user.value?.role_name || null);

    function setAuth(u, t) {
        user.value = u;
        token.value = t;
        localStorage.setItem('user', JSON.stringify(u));
        localStorage.setItem('token', t);
    }

    function clearAuth() {
        user.value = null;
        token.value = null;
        localStorage.removeItem('user');
        localStorage.removeItem('token');
    }

    return {
        user,
        token,
        isAuthenticated,
        userRole,
        setAuth,
        clearAuth
    };
});
