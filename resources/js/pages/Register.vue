<template>
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="card p-8">
                <div class="mb-8 text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-linear-to-r from-purple-600 to-blue-600"
                    >
                        <span class="text-lg font-bold text-white">T</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Ticketeer</h1>
                    <p class="mt-2 text-gray-600">Create your account</p>
                </div>

                <form @submit.prevent="handleRegister" class="space-y-4">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="isOrganizer = false"
                            :class="[
                                !isOrganizer ? 'btn-primary' : 'btn-secondary',
                                'w-1/2',
                            ]"
                        >
                            Buyer
                        </button>
                        <button
                            type="button"
                            @click="isOrganizer = true"
                            :class="[
                                isOrganizer ? 'btn-primary' : 'btn-secondary',
                                'w-1/2',
                            ]"
                        >
                            Organizer
                        </button>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900"
                            >Full Name</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="John Doe"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900"
                            >Email</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="your@email.com"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900"
                            >Password</label
                        >
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            placeholder="••••••••"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900"
                            >Confirm Password</label
                        >
                        <input
                            v-model="form.passwordConfirmation"
                            type="password"
                            required
                            placeholder="••••••••"
                        />
                    </div>

                    <div
                        v-if="error"
                        class="rounded-lg border border-red-200 bg-red-50 p-3"
                    >
                        <p class="text-sm text-red-800">{{ error }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ loading ? 'Creating account...' : 'Sign Up' }}
                    </button>
                </form>

                <div class="mt-6 border-t border-gray-200 pt-6 text-center">
                    <p class="text-gray-600">
                        Already have an account?
                        <router-link
                            to="/login"
                            class="font-semibold text-purple-600 hover:text-purple-700"
                        >
                            Sign in
                        </router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore';
import { api } from '../utils/api';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
    name: '',
    email: '',
    password: '',
    passwordConfirmation: '',
});

const isOrganizer = ref(false);

const loading = ref(false);
const error = ref('');

const handleRegister = async () => {
    if (form.value.password !== form.value.passwordConfirmation) {
        error.value = 'Passwords do not match';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await api.register({
            name: form.value.name,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.passwordConfirmation,
            is_organizer: isOrganizer.value,
        });

        // Mirror what the auth store does: save token & user
        authStore.token = response.token;
        authStore.user = response.user;
        localStorage.setItem('auth_token', response.token);

        router.push('/');
    } catch (err) {
        error.value = (err as Error).message || 'Registration failed';
    } finally {
        loading.value = false;
    }
};
</script>
