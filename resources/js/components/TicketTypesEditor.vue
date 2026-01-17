<template>
    <div class="rounded-lg bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold">Ticket Types</h2>

            <div class="flex items-center gap-3">
                <button
                    v-if="!creating"
                    type="button"
                    class="btn-primary text-sm"
                    @click="startCreating"
                >
                    Add Ticket Type
                </button>
                <button
                    v-else
                    type="button"
                    class="btn-secondary text-sm"
                    @click="cancelCreate"
                    :disabled="saving"
                >
                    Cancel
                </button>
            </div>
        </div>

        <div v-if="loading" class="py-6 text-center">
            <div class="inline-block animate-spin">
                <svg
                    class="h-6 w-6 text-purple-600"
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
            </div>
        </div>

        <!-- Create new ticket type form -->
        <div v-if="creating" class="mb-6 rounded-lg border p-4">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Name</label
                    >
                    <input
                        v-model="newTT.name"
                        type="text"
                        class="mt-1 block w-full rounded-lg border px-3 py-2"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Code</label
                    >
                    <input
                        v-model="newTT.code"
                        type="text"
                        class="mt-1 block w-full rounded-lg border px-3 py-2"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Price (USD)</label
                    >
                    <input
                        v-model.number="newTT.price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full rounded-lg border px-3 py-2"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Total Quantity</label
                    >
                    <input
                        v-model.number="newTT.total_quantity"
                        type="number"
                        min="1"
                        class="mt-1 block w-full rounded-lg border px-3 py-2"
                    />
                </div>
            </div>

            <div class="mt-3">
                <label class="block text-sm font-medium text-gray-700"
                    >Description (optional)</label
                >
                <input
                    v-model="newTT.description"
                    type="text"
                    class="mt-1 block w-full rounded-lg border px-3 py-2"
                />
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button
                    type="button"
                    class="btn-primary"
                    :disabled="saving"
                    @click="create"
                >
                    <span v-if="saving" class="mr-2 inline-block animate-spin">
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
                    Create
                </button>

                <button
                    type="button"
                    class="btn-secondary"
                    :disabled="saving"
                    @click="cancelCreate"
                >
                    Cancel
                </button>

                <div v-if="creatingError" class="ml-4 text-sm text-red-600">
                    {{ creatingError }}
                </div>
            </div>
        </div>

        <!-- List ticket types -->
        <div
            v-if="!loading && ticketTypes.length === 0"
            class="text-sm text-gray-600"
        >
            No ticket types yet.
        </div>

        <div
            v-for="tt in ticketTypes"
            :key="tt.id || tt.__id"
            class="mb-4 rounded-lg border border-gray-200 p-4"
        >
            <div
                v-if="!tt.isEditing"
                class="grid grid-cols-1 gap-4 md:grid-cols-4"
            >
                <div>
                    <div class="font-semibold">{{ tt.name }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        Code: <span class="font-mono">{{ tt.code }}</span>
                    </div>
                </div>

                <div>
                    <div class="text-sm">
                        Price:
                        <span class="font-medium"
                            >${{ formatPrice(tt.price) }}</span
                        >
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                        {{ tt.description }}
                    </div>
                </div>

                <div>
                    <div class="text-sm">
                        Available:
                        <span class="font-medium">{{
                            tt.available_quantity
                        }}</span>
                    </div>
                    <div class="text-sm">
                        Total:
                        <span class="font-medium">{{ tt.total_quantity }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="btn-secondary text-sm"
                        @click="startEdit(tt)"
                    >
                        Edit
                    </button>
                    <!-- Show 'Mark Sold Out' only if sales exist AND not already sold out -->
                    <!-- Show 'Delete' only if NO sales exist -->
                    <button
                        v-if="eventId && tt.id && (Number(tt.total_quantity) > Number(tt.available_quantity))"
                        type="button"
                        class="btn-danger text-sm"
                        @click="markSoldOut(tt)"
                        :disabled="tt.total_quantity - tt.available_quantity === 0"
                    >
                        Mark Sold Out
                    </button>
                    <button
                        v-else
                        type="button"
                        class="btn-danger text-sm"
                        @click="remove(tt)"
                        :disabled="deletingId === (tt.id || tt.__id)"
                    >
                        <span
                            v-if="deletingId === (tt.id || tt.__id)"
                            class="mr-2 inline-block animate-spin"
                        >
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
                        Delete
                    </button>
                </div>
            </div>

            <!-- Edit mode -->
            <div v-else class="space-y-3">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Name</label
                        >
                        <input
                            v-model="tt._edit.name"
                            type="text"
                            class="mt-1 block w-full rounded-lg border px-3 py-2"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Code {{ eventId ? '(immutable)' : '' }}</label
                        >
                        <!-- Code is immutable only if we are editing an existing ticket type on the server -->
                        <input
                            v-model="tt._edit.code"
                            type="text"
                            :disabled="!!eventId && !!tt.id"
                            :class="[
                                'mt-1 block w-full rounded-lg border px-3 py-2',
                                !!eventId && !!tt.id
                                    ? 'bg-gray-50'
                                    : 'bg-white',
                            ]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Price (USD)</label
                        >
                        <input
                            v-model.number="tt._edit.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full rounded-lg border px-3 py-2"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Total Quantity</label
                        >
                        <input
                            v-model.number="tt._edit.total_quantity"
                            type="number"
                            min="1"
                            class="mt-1 block w-full rounded-lg border px-3 py-2"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Description (optional)</label
                    >
                    <input
                        v-model="tt._edit.description"
                        type="text"
                        class="mt-1 block w-full rounded-lg border px-3 py-2"
                    />
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="btn-primary"
                        @click="save(tt)"
                        :disabled="saving || savingId === (tt.id || tt.__id)"
                    >
                        <span
                            v-if="
                                saving && savingId === (tt.id || tt.__id)
                            "
                            class="mr-2 inline-block animate-spin"
                        >
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
                        Save
                    </button>
                    <button
                        type="button"
                        class="btn-secondary"
                        @click="cancelEdit(tt)"
                        :disabled="saving"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <div
                v-if="ttErrors[tt.id || tt.__id]"
                class="mt-2 text-sm text-red-600"
            >
                {{ ttErrors[tt.id || tt.__id] }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/useAuthStore';
import type { TicketType, TicketTypeInput } from '@/types';
import { api } from '@/utils/api';
import { onMounted, reactive, ref, watch } from 'vue';

const props = defineProps<{
    eventId?: string;
    modelValue?: (TicketType | TicketTypeInput)[]; // For local mode
    initialTicketTypes?: TicketType[]; // Legacy prop (keep for back-compat if needed, though modelValue is preferred for initial state in local mode)
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: (TicketType | TicketTypeInput)[]): void;
}>();

/**
 * State
 */
// Initialize from modelValue (local mode) OR initialTicketTypes (legacy/server mode)
const ticketTypes = ref<any[]>(
    props.modelValue ? [...props.modelValue] : props.initialTicketTypes ?? [],
);

const loading = ref(false);
const creating = ref(false);
const saving = ref(false);
const savingId = ref('');
const deletingId = ref('');
const creatingError = ref('');
const ttErrors = reactive<Record<number | string, string>>({});

// Provide a temporary ID generator for local items
const uid = () => Math.random().toString(36).substr(2, 9);

/**
 * New ticket template
 */
const newTT = reactive({
    name: '',
    code: '',
    price: 0,
    total_quantity: 1,
    description: '',
});

/**
 * Auth store
 */
const authStore = useAuthStore();

/**
 * Sync with parent modelValue if it changes externally
 */
watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            // We only replace if the length or contents look significantly different to avoid overwriting local edit state
            // For simplicity, let's assume parent is source of truth but local edits happen here.
            // If parent changes, we might want to respect it.
            // But to avoid cursor jumps or edit resets, we might want to be careful.
            // For now, let's trust that we emit updates and parent consumes them.
            // Re-syncing only if lengths differ is a heuristic.
            if (val.length !== ticketTypes.value.length) {
                 ticketTypes.value = val.map(t => ({
                     ...t,
                     isEditing: false, // reset edit state on external update
                     _edit: {}
                 }));
            }
        }
    },
    { deep: true },
);

function emitUpdate() {
    emit('update:modelValue', ticketTypes.value);
}

/**
 * Helpers
 */
function formatPrice(p: any) {
    return p ? Number(p).toFixed(2) : '0.00';
}

function setItemEditState(item: any) {
    item.isEditing = false;
    item._edit = {
        name: item.name,
        code: item.code, // Include code for local edit
        price: item.price,
        total_quantity: item.total_quantity,
        description: item.description ?? '',
    };
}

/**
 * Load ticket types (fresh) - Only if eventId is present
 */
async function loadTicketTypes() {
    if (!props.eventId) return; // Local mode: do nothing
    loading.value = true;
    try {
        const res = await api.getTicketTypes(props.eventId);
        // normalize items and add editing meta
        ticketTypes.value = (res ?? []).map((t: any) => {
            const copy = { ...t };
            setItemEditState(copy);
            return copy;
        });
        emitUpdate();
    } catch (err: unknown) {
        console.error('Error loading ticket types:', err);
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    // If not in local mode (eventId exists), fetch from server
    if (props.eventId) {
        await loadTicketTypes();
    } else {
        // Local mode initialization
        ticketTypes.value.forEach((t) => setItemEditState(t));
        
        // If empty in local mode, start creating immediately for better UX
        if (ticketTypes.value.length === 0) {
            startCreating();
        }
    }
});

/**
 * Create
 */
function startCreating() {
    creatingError.value = '';
    creating.value = true;
}

function cancelCreate() {
    creatingError.value = '';
    creating.value = false;
    // reset
    newTT.name = '';
    newTT.code = '';
    newTT.price = 0;
    newTT.total_quantity = 1;
    newTT.description = '';
}

async function create() {
    creatingError.value = '';
    // basic validation
    if (!newTT.name?.trim() || !newTT.code?.trim()) {
        creatingError.value = 'Name and code are required.';
        return;
    }
    if (Number(newTT.total_quantity) < 1) {
        creatingError.value = 'Quantity must be at least 1.';
        return;
    }

    // CHECK: Local or Server?
    if (!props.eventId) {
        // LOCAL MODE
        const newItem = {
            __id: uid(), // Temp ID
            id: '', // No database ID yet
            name: newTT.name,
            code: newTT.code,
            price: Number(newTT.price),
            total_quantity: Number(newTT.total_quantity),
            available_quantity: Number(newTT.total_quantity), // Assume full availability
            description: newTT.description || null,
        };
        setItemEditState(newItem);
        ticketTypes.value.push(newItem);
        emitUpdate();
        cancelCreate();
        return;
    }

    // SERVER MODE
    try {
        saving.value = true;
        await authStore.getUser();
        const res = await api.createTicketType(
            props.eventId,
            {
                name: newTT.name,
                code: newTT.code,
                price: Number(newTT.price),
                total_quantity: Number(newTT.total_quantity),
                description: newTT.description || null,
            },
            authStore.token,
        );

        const copy = { ...res };
        setItemEditState(copy);
        ticketTypes.value.push(copy);
        cancelCreate();
    } catch (err: unknown) {
        creatingError.value =
            (err as Error)?.message ?? 'Error creating ticket type';
    } finally {
        saving.value = false;
    }
}

/**
 * Edit / Save
 */
function startEdit(item: any) {
    item._edit = {
        name: item.name,
        code: item.code,
        price: item.price,
        total_quantity: item.total_quantity,
        description: item.description ?? '',
    };
    item.isEditing = true;
    ttErrors[item.id || item.__id] = '';
}

function cancelEdit(item: any) {
    setItemEditState(item); // Revert to original
}

async function save(item: any) {
    const itemId = item.id || item.__id;
    ttErrors[itemId] = '';

    if (!item._edit.name?.trim()) {
        ttErrors[itemId] = 'Name is required.';
        return;
    }
    // Code validation: required
    if (!props.eventId && !item._edit.code?.trim()) {
        ttErrors[itemId] = 'Code is required.';
        return;
    }

    if (Number(item._edit.total_quantity) < 1) {
        ttErrors[itemId] = 'Quantity must be at least 1.';
        return;
    }

    // LOCAL MODE
    if (!props.eventId) {
        item.name = item._edit.name;
        item.code = item._edit.code; // Allow editing code in local mode
        item.price = Number(item._edit.price);
        item.total_quantity = Number(item._edit.total_quantity);
        item.available_quantity = Number(item._edit.total_quantity); // Reset available? Or logic needed? 
                                                                     // For simple creation, total = available usually.
        item.description = item._edit.description || null;
        item.isEditing = false;
        emitUpdate();
        return;
    }

    // SERVER MODE
    try {
        saving.value = true;
        savingId.value = itemId;
        await authStore.getUser();

        const payload = {
            name: item._edit.name,
            price: Number(item._edit.price),
            total_quantity: Number(item._edit.total_quantity),
            description: item._edit.description || null,
        };

        const updated = await api.updateTicketType(
            String(item.id),
            payload,
            authStore.token,
        );

        // replace values
        item.name = updated.name;
        item.price = updated.price;
        item.total_quantity = updated.total_quantity;
        item.available_quantity = updated.available_quantity;
        item.description = updated.description ?? '';
        item.isEditing = false;
    } catch (err: unknown) {
        ttErrors[itemId] =
            (err as Error)?.message ?? 'Error updating ticket type';
    } finally {
        saving.value = false;
        savingId.value = '';
    }
}

/**
 * Delete
 */
async function remove(item: any) {
    // confirm
    if (!confirm(`Delete ticket type "${item.name}"? This cannot be undone.`)) {
        return;
    }

    const itemId = item.id || item.__id;
    ttErrors[itemId] = '';

    // LOCAL MODE
    if (!props.eventId) {
        ticketTypes.value = ticketTypes.value.filter((t) => (t.id || t.__id) !== itemId);
        emitUpdate();
        return;
    }

    // SERVER MODE
    try {
        deletingId.value = itemId;
        await authStore.getUser();
        await api.deleteTicketType(String(item.id), authStore.token);

        // remove from list
        ticketTypes.value = ticketTypes.value.filter((t) => t.id !== item.id);
    } catch (err: unknown) {
        ttErrors[itemId] =
            (err as Error).message ?? 'Error deleting ticket type';
    } finally {
        deletingId.value = '';
    }
}

/**
 * Mark a ticket type as sold out 
 * (Only applicable in Server Mode)
 */
async function markSoldOut(item: any) {
    if (!props.eventId) return;

    ttErrors[item.id] = '';

    const sold = Number(item.total_quantity) - Number(item.available_quantity);
    if (sold <= 0) {
        ttErrors[item.id] =
            'No tickets have been sold yet; cannot mark as sold out.';
        return;
    }

    try {
        saving.value = true;
        savingId.value = item.id;

        await authStore.getUser();
        const updated = await api.updateTicketType(
            String(item.id),
            { total_quantity: sold },
            authStore.token,
        );

        // update local list with the server's response
        const idx = ticketTypes.value.findIndex((t) => t.id === item.id);
        if (idx !== -1) {
            ticketTypes.value[idx] = { ...ticketTypes.value[idx], ...updated };
        }
    } catch (err: unknown) {
        ttErrors[item.id] = (err as Error).message ?? 'Failed to mark sold out';
    } finally {
        saving.value = false;
        savingId.value = '';
    }
}
</script>

<style scoped>
/* Small scoped tweaks to keep UI tidy */
.btn-secondary[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
