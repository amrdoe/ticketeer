<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-7xl px-4">
            <h1 class="mb-8 text-4xl font-bold text-gray-900">All Events</h1>

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
                    <EventCard
                        v-for="event in events"
                        :key="event.id"
                        :event="event"
                    />
                </div>

                <div v-else class="py-12 text-center">
                    <p class="text-lg text-gray-600">No events available</p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="totalPages > 1"
                    class="mt-12 flex justify-center gap-2"
                >
                    <button
                        v-for="page in totalPages"
                        :key="page"
                        @click="currentPage = page"
                        :class="[
                            'rounded-lg px-4 py-2 font-semibold transition',
                            currentPage === page
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
import { onMounted, ref, watch } from 'vue';
import EventCard from '../components/EventCard.vue';
import { api } from '../utils/api';

const events = ref<Event[]>([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const loadEvents = async () => {
    loading.value = true;
    try {
        const response = await api.getEvents(currentPage.value);
        events.value = response.data;
        totalPages.value = response.last_page;
    } catch (error) {
        console.error('Error loading events:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(loadEvents);

watch(() => currentPage.value, loadEvents);
</script>
