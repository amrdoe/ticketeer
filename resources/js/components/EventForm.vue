<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Transition Wrapper -->
        <!-- Transition Wrapper -->
        <Transition :name="transitionName" mode="out-in">
            <!-- WIZARD MODE: Dynamic switching -->
            <div v-if="wizard && currentStep === 1" key="step1" class="w-full">
                 <EventDetails v-model="form" :errors="errors" />
            </div>
            
            <div v-else-if="wizard && currentStep === 2" key="step2" class="w-full">
                 <div v-if="showTicketTypes" class="mt-6">
                    <TicketTypesEditor v-model="form.ticket_types" />
                    <div v-if="errors.ticket_types" class="text-sm text-red-600 mt-2">
                        {{ errors.ticket_types }}
                    </div>
                </div>
            </div>

            <!-- NON-WIZARD MODE: Render everything -->
            <div v-else key="all" class="w-full">
                 <EventDetails v-model="form" :errors="errors" />
                 
                 <div v-if="showTicketTypes" class="mt-6">
                    <TicketTypesEditor v-model="form.ticket_types" />
                    <div v-if="errors.ticket_types" class="text-sm text-red-600 mt-2">
                        {{ errors.ticket_types }}
                    </div>
                </div>
            </div>
        </Transition>

        <div class="flex flex-row-reverse items-center gap-3">
            <template v-if="!wizard || currentStep === 2">
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
            </template>
            
            <button
                v-if="wizard && currentStep === 1"
                type="button"
                class="btn-primary"
                @click="handleNext"
            >
                Next
            </button>

            <button
                v-if="wizard && currentStep === 2"
                type="button"
                class="btn-secondary"
                @click="handleBack"
            >
                Back
            </button>

            <button
                v-if="!wizard || currentStep === 1"
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
import { computed, reactive, ref, watch } from 'vue';
import TicketTypesEditor from './TicketTypesEditor.vue';
import EventDetails from './EventDetails.vue';

const props = withDefaults(defineProps<{
    initialEvent?: Partial<EventInput>;
    loading?: boolean;
    submitText?: string;
    manageTicketTypes?: boolean;
    wizard?: boolean;
}>(), {
    manageTicketTypes: true,
    wizard: false
});

const emit = defineEmits<{
    (e: 'submit', payload: EventInput): void;
    (e: 'cancel'): void;
}>();

const currentStep = ref(1);
const transitionName = ref('slide-left');

const showTicketTypes = computed(() => {
    if (!props.manageTicketTypes) return false;
    return !props.wizard || currentStep.value === 2;
});

const uid = () => Math.random().toString(36).substr(2, 9);
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
    ticket_types: initialTickets.length ? initialTickets : [],
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
        const tt = (next.ticket_types ?? []).map(
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


function validate(step?: number): boolean {
    Object.keys(errors).forEach((k) => delete errors[k]);
    globalError = '';
    
    const checkStep1 = step === undefined || step === 1;
    const checkStep2 = step === undefined || step === 2;

    if (checkStep1) {
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
    }

    if (checkStep2 && showTicketTypes.value) {
        // Validate ticket types presence
        if (!form.ticket_types || form.ticket_types.length === 0) {
            errors.ticket_types = 'At least one ticket type is required.';
        } else {
            // Validate individual ticket types
            form.ticket_types.forEach((tt: any) => {
                  if (!tt.name?.trim() || !tt.code?.trim() || tt.price < 0 || tt.total_quantity < 1) {
                      errors.ticket_types = 'Please correct errors in ticket types (check Name, Code, Price, and Quantity).';
                  }
            });
        }
    }

    return Object.keys(errors).length === 0;
} 

function handleNext() {
    if (validate(1)) {
        transitionName.value = 'slide-left';
        currentStep.value = 2;
    }
}

function handleBack() {
    transitionName.value = 'slide-right';
    currentStep.value = 1;
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

    if (showTicketTypes.value) {
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

<style scoped>
/* Slide Left (Next) */
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
    transition: all 0.3s ease-out;
}

.slide-left-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.slide-left-enter-to {
    opacity: 1;
    transform: translateX(0);
}

.slide-left-leave-from {
    opacity: 1;
    transform: translateX(0);
}
.slide-left-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

/* Slide Right (Back) */
.slide-right-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}
.slide-right-enter-to {
    opacity: 1;
    transform: translateX(0);
}

.slide-right-leave-from {
    opacity: 1;
    transform: translateX(0);
}
.slide-right-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
