<template>
    <div class="min-h-screen py-12">
        <div class="mx-auto max-w-7xl px-4">
            <h1 class="mb-8 text-4xl font-bold text-gray-900">My Tickets</h1>

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
                <div v-if="tickets.length > 0" class="space-y-6">
                    <div
                        v-for="ticket in tickets"
                        :key="ticket.id"
                        class="card overflow-hidden p-8"
                    >
                        <div class="flex flex-col md:flex-row">
                            <!-- QR Code -->
                            <div
                                ref="qrCodeContainer"
                                class="flex w-full flex-col items-center justify-center rounded-xl border bg-gray-50 p-8 md:w-1/3"
                            >
                                <qrcode-vue
                                    :value="
                                        appOrigin +
                                        '/tickets/scan/' +
                                        ticket.unique_code
                                    "
                                    ref="qrCode"
                                    :size="600"
                                    class="mb-2 h-auto! w-full!"
                                    :id="ticket.unique_code"
                                />
                            </div>
                            <!-- Details -->
                            <div
                                class="flex w-full flex-col justify-between p-6 md:w-2/3"
                            >
                                <div>
                                    <div class="mb-2">
                                        <h2
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{
                                                ticket.ticket_type?.event
                                                    ?.title ?? '-'
                                            }}
                                        </h2>
                                    </div>
                                    <div class="mb-2">
                                        <h3 class="text-gray-800">
                                            {{
                                                ticket.ticket_type?.event?.user
                                                    ?.name ?? '-'
                                            }}
                                        </h3>
                                    </div>
                                    <div class="mb-2">
                                        <span
                                            class="mb-1 block text-xs text-gray-500"
                                            >Ticket Type</span
                                        >
                                        <span class="text-gray-800">{{
                                            ticket.ticket_type?.name ?? '-'
                                        }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span
                                            class="mb-1 block text-xs text-gray-500"
                                            >Price</span
                                        >
                                        <span class="text-gray-800"
                                            >${{
                                                parseFloat(
                                                    ticket.ticket_type?.price ??
                                                        0,
                                                ).toFixed(2)
                                            }}</span
                                        >
                                    </div>
                                    <div class="mb-2">
                                        <span
                                            class="mb-1 block text-xs text-gray-500"
                                            >Event Date</span
                                        >
                                        <span class="text-gray-800">
                                            {{
                                                ticket.ticket_type?.event
                                                    ?.start_date
                                                    ? new Date(
                                                          ticket.ticket_type
                                                              .event.start_date,
                                                      ).toLocaleString(
                                                          'en-US',
                                                          {
                                                              month: 'short',
                                                              day: 'numeric',
                                                              year: 'numeric',
                                                              hour: 'numeric',
                                                              minute: '2-digit',
                                                              hour12: true,
                                                          },
                                                      )
                                                    : '-'
                                            }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <span
                                            class="mb-1 block text-xs text-gray-500"
                                            >Purchase Date</span
                                        >
                                        <span class="text-gray-800">
                                            {{
                                                ticket.created_at
                                                    ? new Date(
                                                          ticket.created_at,
                                                      ).toLocaleString(
                                                          'en-US',
                                                          {
                                                              month: 'short',
                                                              day: 'numeric',
                                                              year: 'numeric',
                                                              hour: 'numeric',
                                                              minute: '2-digit',
                                                              hour12: true,
                                                          },
                                                      )
                                                    : '-'
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <span
                                        class="mb-1 block text-xs text-gray-500"
                                        >Ticket Code</span
                                    >
                                    <span
                                        class="text-xs break-all text-gray-400"
                                        >{{ ticket.unique_code }}</span
                                    >

                                    <button
                                        class="btn-primary float-right mt-4"
                                        @click="printQr(ticket)"
                                        type="button"
                                    >
                                        Print QR Code
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="card p-12 text-center">
                    <p class="mb-4 text-lg text-gray-600">
                        You haven't purchased any tickets yet
                    </p>
                    <router-link to="/events" class="btn-primary inline-block">
                        Browse Events
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/useAuthStore';
import { api } from '@/utils/api';
import QrcodeVue from 'qrcode.vue';
import { computed, onMounted, ref } from 'vue';

const authStore = useAuthStore();
const tickets = ref<Ticket[]>([]);
const loading = ref(false);

const appOrigin = computed(() => {
    return window.location.origin;
});

onMounted(async () => {
    loading.value = true;
    try {
        const response = await api.getTickets(authStore.token);
        tickets.value = response.data || response;
    } catch (error) {
        console.error('Error loading tickets:', (error as Error).message);
    } finally {
        loading.value = false;
    }
});

// Print QR code for a ticket
function printQr(ticket: Ticket) {
    const qrcode = document.getElementById(
        ticket.unique_code,
    ) as HTMLCanvasElement | null;

    const dataUrl = qrcode?.toDataURL('image/png');

    const printWindow = window.open('', '_blank', 'width=1000,height=1000');
    if (!printWindow) return;

    printWindow.document.documentElement.innerHTML = `
            <style>
                body { display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .qrcode { text-align: center; }
                .ticket-code { margin-top: 16px; font-size: 14px; color: #888; }
            </style>
            <div class="qrcode">
                <img src="${dataUrl}" alt="QR Code" onload="window.print(); setTimeout(() => window.close(), 100);" />
                <div class="ticket-code">${ticket.unique_code}</div>
            </div>
    `;
    printWindow.document.close();
}
</script>
