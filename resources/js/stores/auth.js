/**
 * Pinia Auth Store
 *
 * Manages authentication state: user data and Sanctum API token.
 * Persists token and user to localStorage for session survival across refreshes.
 */
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api.js';

export const useAuthStore = defineStore('auth', () => {
    // Reactive state — hydrated from localStorage on init
    const token = ref(localStorage.getItem('token') || null);
    const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

    /** Whether the user is currently authenticated */
    const isAuthenticated = computed(() => !!token.value);

    /** Full display name combining first and last name */
    const fullName = computed(() => {
        if (!user.value) return '';
        return [user.value.name, user.value.last_name].filter(Boolean).join(' ');
    });

    /** User initials for avatar (e.g., "AP" for Andres Paz) */
    const userInitials = computed(() => {
        if (!user.value) return '';
        const first = (user.value.name || '')[0] || '';
        const last = (user.value.last_name || '')[0] || '';
        return (first + last).toUpperCase();
    });

    /**
     * Register a new user account.
     *
     * @param {Object} credentials - { name, last_name, email, password, password_confirmation }
     */
    async function register(credentials) {
        const response = await api.post('/register', credentials);
        setAuth(response.data.user, response.data.token);
        return response.data;
    }

    /**
     * Log in with existing credentials.
     *
     * @param {Object} credentials - { email, password }
     */
    async function login(credentials) {
        const response = await api.post('/login', credentials);
        setAuth(response.data.user, response.data.token);
        return response.data;
    }

    /**
     * Log out the current user by revoking the API token.
     */
    async function logout() {
        try {
            await api.post('/logout');
        } catch (e) {
            // Even if the API call fails, clear local state
        }
        clearAuth();
    }

    /**
     * Persist auth data to both reactive state and localStorage.
     */
    function setAuth(userData, tokenValue) {
        user.value = userData;
        token.value = tokenValue;
        localStorage.setItem('token', tokenValue);
        localStorage.setItem('user', JSON.stringify(userData));
    }

    /**
     * Clear all auth data from state and localStorage.
     */
    function clearAuth() {
        user.value = null;
        token.value = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    }

    return {
        token,
        user,
        isAuthenticated,
        fullName,
        userInitials,
        register,
        login,
        logout,
    };
});
