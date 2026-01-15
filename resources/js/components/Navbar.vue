<template>
    <nav class="navbar">
        <div
            class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4"
        >
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <router-link to="/" class="flex items-center gap-2">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-linear-to-r from-purple-500 to-blue-500"
                    >
                        <span class="text-lg font-bold text-white">T</span>
                    </div>
                    <span
                        class="bg-linear-to-r from-purple-500 to-pink-500 bg-clip-text text-xl font-bold text-transparent"
                        >Ticketeer</span
                    >
                </router-link>

                <router-link
                    to="/events"
                    class="font-medium text-gray-600 transition hover:text-purple-500"
                >
                    Events
                </router-link>
            </div>

            <!-- Menu -->
            <div class="flex items-center gap-6">
                <!-- Cart -->
                <router-link
                    v-if="authStore.user"
                    to="/cart"
                    class="relative text-gray-600 transition hover:text-purple-500"
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
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                    <span
                        v-if="cartStore.totalItems > 0"
                        class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white"
                    >
                        {{ cartStore.totalItems }}
                    </span>
                </router-link>

                <!-- My Tickets -->
                <router-link
                    v-if="authStore.user"
                    to="/my-tickets"
                    class="font-medium text-gray-600 transition hover:text-purple-500"
                >
                    My Tickets
                </router-link>

                <!-- My Events (organizers only) -->
                <router-link
                    v-if="authStore.user?.is_organizer"
                    to="/my-events"
                    class="font-medium text-gray-600 transition hover:text-purple-500"
                >
                    My Events
                </router-link>

                <!-- User Menu -->
                <div
                    v-if="authStore.user"
                    class="relative flex items-center gap-4"
                    ref="dropdownWrapper"
                >
                    <div
                        class="flex h-8 cursor-pointer items-center gap-2"
                        @click="showUserDropdown = !showUserDropdown"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-blue-500"
                        >
                            <span class="text-xs font-bold text-white">
                                {{
                                    authStore.user?.name
                                        ?.charAt(0)
                                        .toUpperCase()
                                }}
                            </span>
                        </div>
                        <span
                            class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            {{ authStore.user?.name }}
                        </span>
                        <svg
                            :class="[
                                'h-4 w-4 transition-transform',
                                showUserDropdown ? 'rotate-180' : '',
                            ]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </div>
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 -translate-y-2"
                    >
                        <div
                            v-if="showUserDropdown"
                            class="absolute top-10 right-0 z-20 w-56 overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/10 dark:bg-gray-800"
                        >
                            <div class="px-4 py-3">
                                <div
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ authStore.user?.name }}
                                </div>
                                <div
                                    class="text-xs break-all text-gray-500 dark:text-gray-400"
                                >
                                    {{ authStore.user?.email }}
                                </div>
                            </div>
                            <div
                                class="my-1 border-t border-gray-100 dark:border-gray-700"
                            ></div>
                            <router-link
                                to="/my-tickets"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                @click="showUserDropdown = false"
                            >
                                My Tickets
                            </router-link>

                            <router-link
                                v-if="authStore.user?.is_organizer"
                                to="/my-events"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                @click="showUserDropdown = false"
                            >
                                My Events
                            </router-link>
                            <button
                                @click="handleLogout"
                                class="block w-full cursor-pointer px-4 py-2 text-left text-sm text-red-700 hover:bg-red-100 dark:text-red-200 dark:hover:bg-red-700"
                            >
                                Log out
                            </button>
                        </div>
                    </transition>
                </div>

                <!-- Auth Links -->
                <div v-else class="flex items-center gap-4">
                    <router-link to="/login" class="btn-secondary text-sm">
                        Login
                    </router-link>
                    <router-link to="/register" class="btn-primary text-sm">
                        Sign Up
                    </router-link>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore';
import { useCartStore } from '../stores/useCartStore';

const authStore = useAuthStore();
const cartStore = useCartStore();
const router = useRouter();

const showUserDropdown = ref(false);
const dropdownWrapper = ref<HTMLElement | null>(null);

const handleLogout = async () => {
    try {
        await authStore.logout();
        router.push('/');
    } catch (error) {
        console.error('Logout error:', error);
    }
};

function handleClickOutside(event: MouseEvent) {
    if (
        showUserDropdown.value &&
        dropdownWrapper.value &&
        !dropdownWrapper.value.contains(event.target as Node)
    ) {
        showUserDropdown.value = false;
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>
