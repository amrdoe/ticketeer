<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700"
                    >Title</label
                >
                <input
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    :aria-invalid="!!errors.title"
                />
                <p v-if="errors.title" class="mt-1 text-xs text-red-600">
                    {{ errors.title }}
                </p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700"
                    >Description</label
                >
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    :aria-invalid="!!errors.description"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-xs text-red-600">
                    {{ errors.description }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Start Date</label
                >
                <input
                    v-model="form.start_date"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    :aria-invalid="!!errors.start_date"
                />
                <p v-if="errors.start_date" class="mt-1 text-xs text-red-600">
                    {{ errors.start_date }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >End Date</label
                >
                <input
                    v-model="form.end_date"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    :aria-invalid="!!errors.end_date"
                />
                <p v-if="errors.end_date" class="mt-1 text-xs text-red-600">
                    {{ errors.end_date }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Sale Start</label
                >
                <input
                    v-model="form.sale_start"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    :aria-invalid="!!errors.sale_start"
                />
                <p v-if="errors.sale_start" class="mt-1 text-xs text-red-600">
                    {{ errors.sale_start }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Sale End</label
                >
                <input
                    v-model="form.sale_end"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    :aria-invalid="!!errors.sale_end"
                />
                <p v-if="errors.sale_end" class="mt-1 text-xs text-red-600">
                    {{ errors.sale_end }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Location</label
                >
                <input
                    v-model="form.location"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Image URL</label
                >
                <input
                    v-model="form.image_url"
                    type="url"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                />
            </div>
        </div>

        <div v-if="showTicketTypes" class="mt-6">
            <!-- Use shared TicketTypesEditor in local mode for creation -->
            <TicketTypesEditor v-model="form.ticket_types" />

            <div v-if="errors.ticket_types" class="text-sm text-red-600 mt-2">
                {{ errors.ticket_types }}
            </div>
        </div>

        <div class="flex flex-row-reverse items-center gap-3">
            <button type="submit" :disabled="loading" class="btn-primary">
                <span v-if="loading" class="mr-2 inline-block animate-spin">
                    <svg
                        class="h-4 w-4 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9"
                        />
                    </svg>
                </span>
                {{ submitText }}
            </button>

            <button
                type="button"
                class="btn-secondary"
                @click="$emit('cancel')"
            >
                Cancel
            </button>
        </div>

        <div v-if="globalError" class="text-sm text-red-600">
            {{ globalError }}
        </div>
    </form>
</template>

<script setup lang="ts">
import type { EventInput } from '@/types';
import { reactive, watch } from 'vue';
import TicketTypesEditor from './TicketTypesEditor.vue';

const props = defineProps<{
    initialEvent?: Partial<EventInput>;
    loading?: boolean;
    submitText?: string;
    manageTicketTypes?: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit', payload: EventInput): void;
    (e: 'cancel'): void;
}>();

const uid = () => Math.random().toString(36).substr(2, 9);
const showTicketTypes = props.manageTicketTypes ?? true;
const normalizeTicket = (t: any) => ({
    __id: uid(),
    id: t?.id ?? '',
    name: t?.name ?? '',
    code: t?.code ?? '',
    price: t?.price !== undefined && t?.price !== null ? Number(t.price) : 0,
    total_quantity:
        t?.total_quantity !== undefined && t?.total_quantity !== null
            ? Number(t.total_quantity)
            : 1,
    description: t?.description ?? '',
});

const initialTickets = (
    props.initialEvent?.ticket_types ??
    props.initialEvent?.ticketTypes ??
    []
).map(normalizeTicket);

const form = reactive<EventInput>({
    title: props.initialEvent?.title ?? '',
    description: props.initialEvent?.description ?? '',
    start_date: formatDateForInput(props.initialEvent?.start_date ?? null),
    end_date: formatDateForInput(props.initialEvent?.end_date ?? null),
    sale_start: formatDateForInput(props.initialEvent?.sale_start ?? null),
    sale_end: formatDateForInput(props.initialEvent?.sale_end ?? null),
    location: props.initialEvent?.location ?? '',
    image_url: props.initialEvent?.image_url ?? '',
    ticket_types: initialTickets.length
        ? initialTickets
        : [
              {
                  __id: uid(),
                  name: '',
                  code: '',
                  price: 0,
                  total_quantity: 1,
                  description: '',
              },
          ],
});

watch(
    () => props.initialEvent,
    (next) => {
        if (!next) return;
        form.title = next.title ?? '';
        form.description = next.description ?? '';
        form.start_date = formatDateForInput(next.start_date ?? null);
        form.end_date = formatDateForInput(next.end_date ?? null);
        form.sale_start = formatDateForInput(next.sale_start ?? null);
        form.sale_end = formatDateForInput(next.sale_end ?? null);
        form.location = next.location ?? '';
        form.image_url = next.image_url ?? '';

        // Only reset ticket types if we're not managing them local-first or if explicitly needed
        const tt = (next.ticket_types ?? next.ticketTypes ?? []).map(
            normalizeTicket,
        );
        if (tt.length) {
            form.ticket_types = tt;
        }
    },
    { deep: true },
);

const errors = reactive<Record<string, string>>({});
let globalError = '';

function formatDateForInput<T extends string | null | undefined>(input: T) {
    if (!input) return input;
    return new Date(input).toISOString();
}

function validate(): boolean {
    Object.keys(errors).forEach((k) => delete errors[k]);
    globalError = '';

    if (!form.title || !form.title.trim()) {
        errors.title = 'Title is required.';
    }
    if (!form.description || !form.description.trim()) {
        errors.description = 'Description is required.';
    }
    if (!form.start_date) {
        errors.start_date = 'Start date is required.';
    }
    if (!form.end_date) {
        errors.end_date = 'End date is required.';
    }
    if (form.start_date && form.end_date) {
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        if (end <= start) {
            errors.end_date = 'End date must be after start date.';
        }
    }
    if (form.sale_start && form.sale_end) {
        const s = new Date(form.sale_start);
        const e = new Date(form.sale_end);
        if (e <= s) {
            errors.sale_end = 'Sale end must be after sale start.';
        }
    }

    if (showTicketTypes) {
        // Validate ticket types presence
        if (!form.ticket_types || form.ticket_types.length === 0) {
            errors.ticket_types = 'At least one ticket type is required.';
        } else {
            // Detailed ticket type validation is handled within TicketTypesEditor UI usually, 
            // but for final submission we should check basic validity or rely on the fact 
            // that TicketTypesEditor doesn't allow invalid saves in local mode?
            // Actually, in local mode, TicketTypesEditor updates the modelValue array immediately.
            // We should check if the array items are valid.
            
            form.ticket_types.forEach((tt: any, i: number) => {
                 // Check simplified invalid states
                  if (!tt.name?.trim() || !tt.code?.trim() || tt.price < 0 || tt.total_quantity < 1) {
                      errors.ticket_types = 'Please correct errors in ticket types.';
                  }
            });
        }
    }

    return Object.keys(errors).length === 0;
}

function toPayload(): EventInput {
    const payload: EventInput = {
        title: form.title?.trim() ?? '',
        description: form.description?.trim() ?? '',
        start_date: form.start_date ?? null,
        end_date: form.end_date ?? null,
        sale_start: form.sale_start ?? null,
        sale_end: form.sale_end ?? null,
        location: form.location?.trim() ?? null,
        image_url: form.image_url?.trim() ?? null,
    };

    if (showTicketTypes) {
        payload.ticket_types = (form.ticket_types ?? []).map((t: any) => ({
            name: t.name?.trim() ?? '',
            code: t.code?.trim() ?? '',
            price: Number(t.price) ?? 0,
            total_quantity: Number(t.total_quantity) ?? 1,
            description: t.description?.trim() ?? null,
        }));
    }

    return payload;
}

async function handleSubmit() {
    try {
        if (!validate()) {
            return;
        }
        emit('submit', toPayload());
    } catch (err: unknown) {
        globalError = (err as Error).message || 'An error occurred';
    }
}
</script>
