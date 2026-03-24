import { defineStore } from 'pinia';
import api from '../src/api/client'; 


export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('tm_token') || null
    }),

    actions: {
        setUser(user) {
            this.user = user;
        },

        setToken(token) {
            this.token = token;
            localStorage.setItem('tm_token', token);
        },

        logout() {
            this.user = null;
            this.token = null;
            localStorage.removeItem('tm_token');
            window.location.href = '/login'

        },

        async checkAuth() {
            if (!this.token) return false;

            try {
                const { data } = await api.get('/me');
                this.user = data;
                return true;
            } catch (err) {
                this.logout();
                return false;
            }
        }
    }
});