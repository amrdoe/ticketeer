<template>
    <div class="min-h-screen">
        <!-- Hero Section -->
        <section class="gradient-primary py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 text-center">
                <h1 class="mb-4 text-5xl font-bold">Discover Amazing Events</h1>
                <p class="mb-8 text-xl text-gray-100">
                    Book tickets for concerts, conferences, and more
                </p>
                <router-link to="/events" class="btn-secondary inline-block">
                    Browse Events
                </router-link>
            </div>
        </section>

        <!-- Featured Events -->
        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4">
                <h2 class="mb-8 text-3xl font-bold text-gray-900">
                    Featured Events
                </h2>

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

                <div
                    v-else-if="events.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <EventCard
                        v-for="event in events"
                        :key="event.id"
                        :event="event"
                    />
                </div>

                <div v-else class="py-12 text-center">
                    <p class="text-lg text-gray-600">
                        No events available at the moment
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import EventCard from '../components/EventCard.vue';
import { api } from '../utils/api';

const events = ref([]);
const loading = ref(false);

onMounted(async () => {
    loading.value = true;
    try {
        const response = await api.getFeaturedEvents();
        events.value = response;
    } catch (error) {
        console.error('Error loading featured events:', error);
    } finally {
        loading.value = false;
    }
});
</script>
