<template>
    <router-link :to="`/events/${event.id}`" class="card overflow-hidden">
        <!-- Image -->
        <div
            v-if="event.image_url"
            class="bg-gradient-primary relative h-48 overflow-hidden"
        >
            <img
                :src="event.image_url"
                :alt="event.title"
                class="gradient-primary h-full w-full object-cover"
                onerror="
                    this.src =
                        'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
                "
            />
            <!-- Date Pill -->
            <div
                class="absolute bottom-3 left-3 flex items-center gap-1 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-purple-500 shadow-md"
            >
                {{ formatDate(event.start_date) }}
            </div>
        </div>
        <div v-else class="gradient-primary relative h-48">
            <!-- Date Pill -->
            <div
                class="absolute bottom-3 left-3 flex items-center gap-1 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-purple-500 shadow-md"
            >
                {{ formatDate(event.start_date) }}
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <h3 class="mb-2 line-clamp-2 text-lg font-bold text-gray-900">
                {{ event.title }}
            </h3>

            <div
                class="mb-3 flex items-center justify-between text-sm text-gray-600"
            >
                <span class="font-semibold">{{ event.user.name }}</span>
                <span
                    class="ml-2 flex items-center gap-1 text-xs font-medium text-purple-600 hover:underline"
                >
                    <span class="font-bold">View Details</span> &#8594;
                </span>
            </div>

            <!-- Date (moved to image pill) -->

            <!-- Price -->
            <div
                v-if="event.ticket_types && event.ticket_types.length > 0"
                class="flex items-center justify-between"
            >
                <span class="text-sm text-gray-600">Starting from</span>
                <span class="text-lg font-bold text-purple-600"
                    >${{ minPrice }}</span
                >
            </div>
        </div>
    </router-link>
</template>

<script setup lang="ts">
import type { Event, TicketType } from '@/types';
import formatDate from '@/utils/formatDate';
import { computed } from 'vue';

const props = defineProps({
    event: {
        type: Object as () => Event,
        required: true,
    },
});

const minPrice = computed(() => {
    if (!props.event.ticket_types?.length) {
        return '0.00';
    }
    const min = Math.min(
        ...props.event.ticket_types.map((t: TicketType) => Number(t.price)),
    );
    return min.toFixed(2);
});
</script>
