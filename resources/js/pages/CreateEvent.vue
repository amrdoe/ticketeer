<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-3xl px-4">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Create Event</h1>
                <router-link to="/my-events" class="btn-secondary"
                    >Back</router-link
                >
            </div>

            <div v-if="loading" class="py-12 text-center">
                <div class="inline-block animate-spin">
                    <svg
                        class="h-8 w-8 text-purple-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                </div>
            </div>

            <div v-else class="rounded-lg bg-white p-6 shadow">
                <EventForm
                    :initialEvent="initialEvent"
                    :loading="loading"
                    submitText="Create Event"
                    @submit="handleCreate"
                    @cancel="goBack"
                />

                <div v-if="error" class="mt-4 text-sm text-red-600">
                    {{ error }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import EventForm from '../components/EventForm.vue';
import { useAuthStore } from '../stores/useAuthStore';
import type { EventInput } from '../types';
import { api } from '../utils/api';

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const error = ref('');
const initialEvent = ref<EventInput>({});

function goBack() {
    router.push('/my-events');
}

async function handleCreate(payload: any) {
    loading.value = true;
    error.value = '';

    try {
        // Ensure auth token is available and valid
        await authStore.getUser();

        await api.createEvent(payload, authStore.token);

        // Redirect to My Events so the user can see the created event in the list
        router.push('/my-events');
    } catch (err: unknown) {
        // API wrapper throws Errors with message; show it to the user
        if (err instanceof Error) {
            error.value = err.message;
        } else {
            error.value = String(err);
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
/* Keep page-specific styles minimal - global utilities handle most layout */
</style>
