<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-7xl px-4">
            <h1 class="mb-8 text-4xl font-bold text-gray-900">Shopping Cart</h1>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div v-if="cartStore.items.length > 0" class="space-y-4">
                        <div
                            v-for="item in cartStore.items"
                            :key="item.ticket_type_id"
                            class="card p-6"
                        >
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ item.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Code: {{ item.code }}
                                    </p>
                                </div>
                                <button
                                    @click="
                                        cartStore.removeItem(
                                            item.ticket_type_id,
                                        )
                                    "
                                    class="text-red-600 transition hover:text-red-800"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="
                                            cartStore.updateQuantity(
                                                item.ticket_type_id,
                                                item.quantity - 1,
                                            )
                                        "
                                        class="rounded bg-gray-200 px-3 py-1 transition hover:bg-gray-300"
                                    >
                                        -
                                    </button>
                                    <span
                                        class="w-12 text-center font-semibold"
                                        >{{ item.quantity }}</span
                                    >
                                    <button
                                        @click="
                                            cartStore.updateQuantity(
                                                item.ticket_type_id,
                                                item.quantity + 1,
                                            )
                                        "
                                        class="rounded bg-gray-200 px-3 py-1 transition hover:bg-gray-300"
                                    >
                                        +
                                    </button>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm text-gray-600">
                                        ${{ item.unit_price }}
                                        each
                                    </p>
                                    <p
                                        class="text-lg font-bold text-purple-600"
                                    >
                                        ${{ item.unit_price * item.quantity }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="card p-12 text-center">
                        <p class="mb-4 text-lg text-gray-600">
                            Your cart is empty
                        </p>
                        <router-link
                            to="/events"
                            class="btn-primary inline-block"
                        >
                            Continue Shopping
                        </router-link>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="card sticky top-20 p-6">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900">
                            Order Summary
                        </h2>

                        <div
                            class="mb-6 space-y-4 border-b border-gray-200 pb-6"
                        >
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold"
                                    >${{
                                        cartStore.totalPrice.toFixed(2)
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (0%)</span>
                                <span class="font-semibold">$0.00</span>
                            </div>
                        </div>

                        <div class="mb-6 flex justify-between">
                            <span class="text-lg font-bold">Total</span>
                            <span class="text-2xl font-bold text-purple-600"
                                >${{ cartStore.totalPrice.toFixed(2) }}</span
                            >
                        </div>

                        <button
                            v-if="cartStore.items.length > 0"
                            @click="handleCheckout"
                            :disabled="loading"
                            class="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                loading
                                    ? 'Processing...'
                                    : 'Proceed to Checkout'
                            }}
                        </button>

                        <router-link
                            v-else
                            to="/events"
                            class="btn-secondary block w-full text-center"
                        >
                            Continue Shopping
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore';
import { useCartStore } from '../stores/useCartStore';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();
const loading = ref(false);

const handleCheckout = async () => {
    if (!authStore.user) {
        router.push('/login');
        return;
    }

    loading.value = true;
    try {
        // Validate cart
        await cartStore.validateCart();
        // Redirect to checkout
        router.push('/checkout');
    } catch (error) {
        alert('Error validating cart: ' + (error as Error).message);
    } finally {
        loading.value = false;
    }
};
</script>
