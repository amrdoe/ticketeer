<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-7xl px-4">
            <h1 class="mb-8 text-4xl font-bold text-gray-900">Checkout</h1>

            <div
                v-if="cartStore.items.length === 0"
                class="card p-12 text-center"
            >
                <p class="mb-4 text-lg text-gray-600">Your cart is empty</p>
                <router-link to="/events" class="btn-primary inline-block">
                    Continue Shopping
                </router-link>
            </div>

            <div v-else class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Order Summary -->
                <div class="lg:col-span-2">
                    <div class="card mb-8 p-6">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900">
                            Order Summary
                        </h2>

                        <div class="space-y-4">
                            <div
                                v-for="item in cartStore.items"
                                :key="item.ticket_type_id"
                                class="flex justify-between border-b border-gray-200 pb-4"
                            >
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ item.name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Qty: {{ item.quantity }}
                                    </p>
                                </div>
                                <p class="font-bold text-gray-900">
                                    ${{
                                        (
                                            item.unit_price * item.quantity
                                        ).toFixed(2)
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex justify-between pt-4 text-lg font-bold"
                            >
                                <span>Total:</span>
                                <span class="text-purple-600"
                                    >${{
                                        cartStore.totalPrice.toFixed(2)
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div class="card p-6">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900">
                            Payment Details
                        </h2>

                        <div
                            id="card-element"
                            class="mb-6 rounded-lg border border-gray-300 p-4"
                        ></div>

                        <div id="card-errors" class="mb-4 text-red-600"></div>

                        <button
                            @click="handlePayment"
                            :disabled="loading"
                            class="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                loading
                                    ? 'Processing Payment...'
                                    : `Pay $${cartStore.totalPrice.toFixed(2)}`
                            }}
                        </button>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="lg:col-span-1">
                    <div class="card sticky top-20 p-6">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900">
                            Order Details
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Order Number
                                </p>
                                <p class="font-semibold text-gray-900">
                                    {{ orderNumber }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Total Items</p>
                                <p class="font-semibold text-gray-900">
                                    {{ cartStore.totalItems }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">
                                    Total Amount
                                </p>
                                <p class="text-2xl font-bold text-purple-600">
                                    ${{ cartStore.totalPrice.toFixed(2) }}
                                </p>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <p class="mb-2 text-sm text-gray-600">
                                    Customer
                                </p>
                                <p class="font-semibold text-gray-900">
                                    {{ authStore.user?.name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ authStore.user?.email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Stripe, StripeElements } from '@stripe/stripe-js';
import type { StripeCardElement } from '@stripe/stripe-js/dist/stripe-js/elements/card';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore';
import { useCartStore } from '../stores/useCartStore';
import { api } from '../utils/api';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const loading = ref(false);
const orderNumber = ref(
    'ORD-' + Math.random().toString(36).substring(2, 11).toUpperCase(),
);
let stripe: Stripe | undefined;
let elements: StripeElements | undefined;
let cardElement: StripeCardElement | undefined;

const handlePayment = async () => {
    if (!stripe || !cardElement) {
        alert('Payment system not initialized');
        return;
    }

    loading.value = true;

    try {
        // Create order
        const orderResponse = await api.createOrder(
            cartStore.items.map((item) => ({
                ticket_type_id: item.ticket_type_id,
                quantity: item.quantity,
            })),
            authStore.token,
        );

        const orderId = orderResponse.id;

        // Create payment intent
        const paymentResponse = await api.createPaymentIntent(
            orderId,
            authStore.token,
        );

        // Confirm payment with Stripe
        const { error, paymentIntent } = await stripe.confirmCardPayment(
            paymentResponse.clientSecret,
            {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: authStore.user?.name,
                        email: authStore.user?.email,
                    },
                },
            },
        );

        if (error) {
            alert('Payment failed: ' + error.message);
        } else if (paymentIntent.status === 'succeeded') {
            // Confirm payment on server
            await api.confirmPayment(
                orderId,
                paymentIntent.id,
                authStore.token,
            );

            // Clear cart and redirect
            cartStore.clear();
            router.push(`/order-confirmation/${orderId}`);
        }
    } catch (error) {
        alert('Error: ' + (error as Error).message);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    // Load Stripe
    const stripePublicKey = document.querySelector<HTMLMetaElement>(
        'meta[name="stripe-public-key"]',
    )?.content;
    if (stripePublicKey) {
        stripe = window.Stripe?.(stripePublicKey);
        elements = stripe?.elements();
        cardElement = elements?.create('card');
        cardElement?.mount('#card-element');

        cardElement?.on('change', (event) => {
            const displayError = document.getElementById('card-errors');
            if (!displayError) return;
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
});
</script>
