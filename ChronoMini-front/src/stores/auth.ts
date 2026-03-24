import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import * as AccountService from '@/_services/AccountService';
import type { User } from '@/_models/User';
import type { Credentials } from '@/_models/Credentials';

export const useAuthStore = defineStore('auth', () => {
    // Utiliser directement le type User
    const user = ref<User | null>(null);
    const isAuthenticated = ref(false);
    
    // Créer une propriété calculée pour vérifier si l'utilisateur est admin
    const isAdmin = computed(() => {
        return user.value?.role_id === 1;
    });
    
    async function login(credentials: Credentials) {
        try {
            const loggedInUser = await AccountService.login(credentials);
            user.value = loggedInUser;  // Stocke l'utilisateur complet
            isAuthenticated.value = true;
            return loggedInUser;
        } catch (error) {
            isAuthenticated.value = false;
            throw error;
        }
    }

    async function logout() {
        try {
            await AccountService.logout();
            user.value = null;
            isAuthenticated.value = false;
        } catch (error) {
        throw error;       }
    }

    return { user, isAuthenticated, isAdmin, login, logout };
}, {
    persist: {
        pick: ['user', 'isAuthenticated'],
        storage: localStorage,
    }
});