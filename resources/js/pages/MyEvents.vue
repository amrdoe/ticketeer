<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-7xl px-4">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-4xl font-bold text-gray-900">My Events</h1>
                <router-link to="/events/create" class="btn-primary">
                    Create Event
                </router-link>
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
                    v-if="events.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="event in events"
                        :key="event.id"
                        class="relative rounded-lg border border-gray-100 bg-white p-4 shadow-sm"
                    >
                        <EventCard :event="event" />

                        <div class="mt-3 flex justify-end gap-2">
                            <router-link
                                :to="`/events/${event.id}/edit`"
                                class="btn-secondary text-sm"
                            >
                                Edit
                            </router-link>

                            <button
                                @click="handleDelete(event.id)"
                                class="rounded-lg px-3 py-2 text-sm text-red-700 hover:bg-red-100"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="py-12 text-center">
                    <p class="text-lg text-gray-600">
                        You haven't created any events yet.
                    </p>
                    <router-link
                        to="/events/create"
                        class="btn-primary mt-4 inline-block"
                    >
                        Create your first event
                    </router-link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="totalPages > 1"
                    class="mt-12 flex justify-center gap-2"
                >
                    <button
                        v-for="page in totalPages"
                        :key="page"
                        @click="currentPage = Number(page)"
                        :class="[
                            'rounded-lg px-4 py-2 font-semibold transition',
                            currentPage === Number(page)
                                ? 'bg-purple-600 text-white'
                                : 'bg-gray-200 text-gray-900 hover:bg-gray-300',
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Event } from '@/types';
import { onMounted, ref, watch } from 'vue';
import EventCard from '../components/EventCard.vue';
import { useAuthStore } from '../stores/useAuthStore';
import { api } from '../utils/api';

const events = ref<Event[]>([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const authStore = useAuthStore();

const loadEvents = async () => {
    loading.value = true;
    try {
        // Ensure we have the latest user/token
        await authStore.getUser();

        const response = await api.getMyEvents(
            currentPage.value,
            authStore.token,
        );
        events.value = response.data;
        totalPages.value = response.last_page;
    } catch (error) {
        console.error('Error loading my events:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(loadEvents);

watch(() => currentPage.value, loadEvents);

/**
 * Delete an event after confirmation then refresh the list
 */
const handleDelete = async (id: string) => {
    if (
        !confirm(
            'Are you sure you want to delete this event? This action cannot be undone.',
        )
    ) {
        return;
    }

    try {
        loading.value = true;
        await api.deleteEvent(id, authStore.token);
        // Refresh the list after deletion to keep pagination accurate
        await loadEvents();
    } catch (error) {
        console.error('Error deleting event:', error);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped></style>
