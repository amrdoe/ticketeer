<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-2xl px-4">
            <div class="card p-12 text-center">
                <div class="mb-6">
                    <svg
                        class="mx-auto h-16 w-16 text-green-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>

                <h1 class="mb-2 text-4xl font-bold text-gray-900">
                    Order Confirmed!
                </h1>
                <p class="mb-8 text-lg text-gray-600">
                    Thank you for your purchase
                </p>

                <div
                    v-if="order"
                    class="mb-8 rounded-lg bg-gray-50 p-6 text-left"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Order Number</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ order.order_number }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Order Date</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="text-lg font-bold text-purple-600">
                                ${{ parseFloat(order.total_amount).toFixed(2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-lg font-bold text-green-600">
                                {{ order.status }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h3 class="mb-4 font-bold text-gray-900">
                            Order Items
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex justify-between"
                            >
                                <span class="text-gray-700"
                                    >{{ item.ticket_type.name }} x
                                    {{ item.quantity }}</span
                                >
                                <span class="font-semibold text-gray-900"
                                    >${{
                                        parseFloat(item.subtotal).toFixed(2)
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center gap-4">
                    <router-link to="/my-tickets" class="btn-primary">
                        View My Tickets
                    </router-link>
                    <router-link to="/events" class="btn-secondary">
                        Continue Shopping
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore';
import type { Order } from '@/types';
import { api } from '../utils/api';

import formatDate from '../utils/formatDate';

const route = useRoute();
const authStore = useAuthStore();
const order = ref<Order | null>(null);

onMounted(async () => {
    try {
        const response = await api.getOrder(
            route.params.orderId as string,
            authStore.token,
        );
        order.value = response;
    } catch (error) {
        console.error('Error loading order:', error);
    }
});
</script>
