<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-7xl px-4">
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

            <div v-else-if="event">
                <!-- Hero Image -->
                <div
                    class="gradient-primary mb-8 flex h-96 items-center justify-center overflow-hidden rounded-lg"
                >
                    <img
                        v-if="validImage"
                        :src="event.image_url"
                        :alt="event.title"
                        class="h-full w-full object-cover"
                        @error="validImage = false"
                    />
                    <span class="text-8xl font-bold text-white" v-else>{{
                        event.title
                    }}</span>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Event Details -->
                    <div>
                        <h1 class="mb-4 text-4xl font-bold text-gray-900">
                            Event Details
                        </h1>

                        <div class="prose prose-sm mb-8 max-w-none">
                            <p class="leading-relaxed text-gray-700">
                                {{ event.description }}
                            </p>
                        </div>

                        <div class="mb-8 space-y-4">
                            <div class="flex items-center gap-3">
                                <strong>Organizer:</strong>
                                <span class="text-gray-700">{{
                                    event.user.name
                                }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <strong>Start:</strong>
                                <span class="text-gray-700">{{
                                    formatDate(event.start_date)
                                }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <strong>End:</strong>
                                <span class="text-gray-700">{{
                                    formatDate(event.end_date)
                                }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <strong>Sale Period:</strong>
                                <span class="text-gray-700"
                                    >{{ formatDate(event.sale_start) }} -
                                    {{ formatDate(event.sale_end) }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="card sticky top-20 p-6">
                            <h2 class="mb-6 text-2xl font-bold text-gray-900">
                                Available Tickets
                            </h2>

                            <div
                                v-if="ticketTypes.length > 0"
                                class="space-y-4"
                            >
                                <div
                                    v-for="ticket in ticketTypes"
                                    :key="ticket.id"
                                    class="rounded-lg border border-gray-200 p-4"
                                >
                                    <div
                                        class="mb-3 flex items-start justify-between"
                                    >
                                        <div>
                                            <h3 class="font-bold text-gray-900">
                                                {{ ticket.name }}
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                Code: {{ ticket.code }}
                                            </p>
                                        </div>
                                        <span
                                            class="text-lg font-bold text-purple-600"
                                            >${{
                                                Number(ticket.price).toFixed(2)
                                            }}</span
                                        >
                                    </div>

                                    <p
                                        v-if="ticket.description"
                                        class="mb-3 text-sm text-gray-600"
                                    >
                                        {{ ticket.description }}
                                    </p>

                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p class="mb-4 text-sm text-gray-600">
                                            Available:
                                            <span class="font-semibold">{{
                                                ticket.available_quantity
                                            }}</span>
                                        </p>

                                        <div
                                            class="mb-4 flex items-center gap-2"
                                        >
                                            <input
                                                v-model.number="
                                                    quantities[ticket.id]
                                                "
                                                type="number"
                                                min="0"
                                                :max="ticket.available_quantity"
                                                class="w-fit rounded border border-gray-300 text-center"
                                            />

                                            <button
                                                @click="addToCart(ticket)"
                                                :disabled="
                                                    quantities[ticket.id] ===
                                                        0 ||
                                                    quantities[ticket.id] ===
                                                        undefined
                                                "
                                                class="btn-primary w-fit text-sm whitespace-nowrap disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="py-8 text-center">
                                <p class="text-gray-600">
                                    No tickets available
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="py-12 text-center">
                <p class="text-lg text-gray-600">Event not found</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useCartStore } from '../stores/useCartStore';
import type { Event, TicketType } from '@/types';
import { api } from '../utils/api';
import formatDate from '../utils/formatDate';

const route = useRoute();
const cartStore = useCartStore();

const event = ref<Event | null>(null);
const ticketTypes = ref<TicketType[]>([]);
const loading = ref(false);
const quantities = ref<Record<string, number>>({});
const validImage = ref(true);

const addToCart = (ticket: TicketType) => {
    const quantity = quantities.value[ticket.id];
    if (quantity && quantity > 0) {
        for (let i = 0; i < quantity; i++) {
            cartStore.addItem(ticket);
        }
        quantities.value[ticket.id] = 0;
        alert('Added to cart!');
    }
};

onMounted(async () => {
    loading.value = true;
    try {
        const response = await api.getEvent(route.params.id as string);
        event.value = response;
        ticketTypes.value = response.ticket_types || [];

        ticketTypes.value.forEach((ticket) => {
            quantities.value[ticket.id] = 0;
        });
    } catch (error) {
        console.error('Error loading event:', error);
    } finally {
        loading.value = false;
    }
});
</script>
