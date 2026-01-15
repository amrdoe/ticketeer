<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-3xl px-4">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Edit Event</h1>
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

            <div v-else>
                <div
                    v-if="error"
                    class="rounded-lg bg-white p-6 text-sm text-red-600"
                >
                    {{ error }}
                </div>

                <div v-else class="rounded-lg bg-white p-6 shadow">
                    <EventForm
                        v-if="event"
                        :initialEvent="event"
                        :loading="saving"
                        submitText="Save Changes"
                        :manageTicketTypes="false"
                        @submit="handleUpdate"
                        @cancel="goBack"
                    />

                    <div class="mt-6">
                        <TicketTypesEditor
                            v-if="event"
                            :eventId="String(event.id)"
                            :initialTicketTypes="
                                event.ticket_types ?? event.ticketTypes ?? []
                            "
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import EventForm from '../components/EventForm.vue';
import TicketTypesEditor from '../components/TicketTypesEditor.vue';
import { useAuthStore } from '../stores/useAuthStore';
import { api } from '../utils/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const id = String(route.params.id ?? '');

const event = ref<any | null>(null);
const loading = ref<boolean>(true);
const saving = ref<boolean>(false);
const error = ref<string>('');

async function fetchEvent() {
    if (!id) {
        router.push('/my-events');
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        // Ensure we have the latest user/token
        await authStore.getUser();

        const response = await api.getEvent(id);
        event.value = response;

        // Check ownership: only owner should be able to edit
        const user = authStore.user;
        const ownerId =
            event.value?.user?.id ??
            event.value?.user_id ??
            event.value?.user?.id ??
            null;

        if (!user || Number(ownerId) !== Number(user.id)) {
            // Not authorized to edit this event
            router.push('/my-events');
            return;
        }
    } catch (err: unknown) {
        console.error('Error loading event:', err);
        // Redirect back to list for not found / other errors
        router.push('/my-events');
    } finally {
        loading.value = false;
    }
}

onMounted(fetchEvent);

function goBack() {
    router.push('/my-events');
}

async function handleUpdate(payload: any) {
    saving.value = true;
    error.value = '';

    try {
        await authStore.getUser();
        await api.updateEvent(id, payload, authStore.token);
        router.push('/my-events');
    } catch (err: unknown) {
        console.error('Error updating event:', err);
        if (err instanceof Error) {
            error.value = err.message;
        } else {
            error.value = String(err);
        }
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped></style>
