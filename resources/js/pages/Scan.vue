<template>
    <div
        class="mx-auto mt-12 max-w-lg rounded bg-white p-6 shadow dark:bg-gray-900"
    >
        <h1 class="mb-4 text-2xl font-bold">Scan Ticket</h1>
        <div v-if="loading" class="flex items-center justify-center py-12">
            <span class="animate-pulse text-gray-500 dark:text-gray-400"
                >Loading ticket...</span
            >
        </div>
        <div v-else-if="error" class="mb-4 text-red-600 dark:text-red-400">
            {{ error }}
        </div>
        <div v-else>
            <div class="mb-4">
                <div class="text-lg font-semibold">
                    Event:
                    <span class="font-normal">{{
                        ticket?.orderItem?.ticket_type?.event?.title
                    }}</span>
                </div>
                <div class="text-lg font-semibold">
                    Ticket Code:
                    <span class="font-mono font-normal">{{
                        ticket?.unique_code
                    }}</span>
                </div>
                <div class="text-lg font-semibold">
                    Status:
                    <span
                        :class="
                            ticket?.redeemed_at
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-yellow-600 dark:text-yellow-400'
                        "
                    >
                        {{ ticket?.redeemed_at ? 'Redeemed' : 'Not Redeemed' }}
                    </span>
                </div>
                <div class="text-lg font-semibold">
                    Ticket Type:
                    <span class="font-normal">{{
                        ticket?.orderItem?.ticket_type?.name
                    }}</span>
                </div>
            </div>
            <div v-if="ticket?.redeemed_at" class="mb-4">
                <div
                    class="rounded bg-green-100 p-3 text-green-800 dark:bg-green-900 dark:text-green-200"
                >
                    This ticket has already been redeemed.
                </div>
            </div>
            <div v-else>
                <div
                    v-if="redeemError"
                    class="mb-2 text-red-600 dark:text-red-400"
                >
                    {{ redeemError }}
                </div>
                <div
                    v-if="redeemSuccess"
                    class="mb-2 text-green-600 dark:text-green-400"
                >
                    {{ redeemSuccess }}
                </div>
                <div v-if="!showConfirm">
                    <button
                        class="rounded bg-blue-600 px-6 py-2 font-semibold text-white transition hover:bg-blue-700"
                        @click="showConfirm = true"
                    >
                        Redeem Ticket
                    </button>
                </div>
                <div v-else class="mt-4">
                    <div class="mb-2 text-gray-700 dark:text-gray-200">
                        Are you sure you want to redeem this ticket? This action
                        cannot be undone.
                    </div>
                    <div class="flex gap-4">
                        <button
                            class="rounded bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
                            @click="redeemTicket"
                            :disabled="redeeming"
                        >
                            <span v-if="redeeming" class="animate-pulse"
                                >Redeeming...</span
                            >
                            <span v-else>Yes, Redeem</span>
                        </button>
                        <button
                            class="rounded bg-gray-300 px-4 py-2 font-semibold text-gray-800 transition dark:bg-gray-700 dark:text-gray-200"
                            @click="showConfirm = false"
                            :disabled="redeeming"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/useAuthStore';
import type { Ticket } from '@/types';
import { api } from '@/utils/api';
import { onMounted, ref } from 'vue';

const authStore = useAuthStore();

const props = defineProps({
    code: {
        type: String,
        required: true,
    },
});

const ticket = ref<Ticket | null>(null);
const loading = ref(true);
const error = ref('');
const showConfirm = ref(false);
const redeeming = ref(false);
const redeemError = ref('');
const redeemSuccess = ref('');

const fetchTicket = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await api.scanTicket(props.code, authStore.token);
        ticket.value = response.data || response;
    } catch (error) {
        console.error('Error loading tickets:', (error as Error).message);
    } finally {
        loading.value = false;
    }
};

const redeemTicket = async () => {
    redeemError.value = '';
    redeemSuccess.value = '';
    redeeming.value = true;
    try {
        const response = await api.redeemTicket(props.code, authStore.token);
        redeemSuccess.value =
            response.data.message || 'Ticket redeemed successfully.';
        ticket.value = response.data.ticket;
        showConfirm.value = false;
    } catch (err) {
        redeemError.value = 'Error loading tickets: ' + (err as Error).message;
    } finally {
        redeeming.value = false;
    }
};

onMounted(() => {
    fetchTicket();
});
</script>
