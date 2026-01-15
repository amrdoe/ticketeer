import type { User } from '@/types';
import { api } from '@/utils/api';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const token = ref(localStorage.getItem('auth_token') ?? '');
    const loading = ref(false);
    const error = ref<string>('');

    const register = async (
        name: string,
        email: string,
        password: string,
        passwordConfirmation: string,
        isOrganizer = false,
    ) => {
        loading.value = true;
        error.value = '';

        try {
            const response = await api.register({
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
                is_organizer: isOrganizer,
            });

            token.value = response.token;
            user.value = response.user;
            localStorage.setItem('auth_token', token.value);

            return response;
        } catch (err) {
            error.value = (err as Error).message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const login = async (email: string, password: string) => {
        loading.value = true;
        error.value = '';

        try {
            const response = await api.login({ email, password });

            token.value = response.token;
            user.value = response.user;
            localStorage.setItem('auth_token', token.value);

            return response;
        } catch (err) {
            error.value = (err as Error).message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        loading.value = true;
        error.value = '';

        try {
            await api.logout(token.value);

            user.value = null;
            token.value = '';
            localStorage.removeItem('auth_token');
        } catch (err) {
            error.value = (err as Error).message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getUser = async (): Promise<User | null> => {
        if (user.value) return user.value;
        if (!token.value) return null;

        loading.value = true;
        error.value = '';

        try {
            const response = await api.getMe(token.value);
            user.value = response;
            return user.value;
        } catch (err) {
            error.value = (err as Error).message;
            token.value = '';
            localStorage.removeItem('auth_token');
            return null;
        } finally {
            loading.value = false;
        }
    };

    const updateProfile = async (data: any) => {
        loading.value = true;
        error.value = '';

        try {
            const response = await api.updateProfile(data, token.value);
            user.value = response.user;
            return response;
        } catch (err) {
            error.value = (err as Error).message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        user,
        token,
        loading,
        error,
        register,
        login,
        logout,
        getUser,
        updateProfile,
    };
});
