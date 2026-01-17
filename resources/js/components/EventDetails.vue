<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input
                v-model="form.title"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                :aria-invalid="!!errors?.title"
            />
            <p v-if="errors?.title" class="mt-1 text-xs text-red-600">
                {{ errors?.title }}
            </p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                :aria-invalid="!!errors?.description"
            ></textarea>
            <p v-if="errors?.description" class="mt-1 text-xs text-red-600">
                {{ errors?.description }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Start Date</label>
            <input
                v-model="form.start_date"
                type="datetime-local"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                :aria-invalid="!!errors?.start_date"
            />
            <p v-if="errors?.start_date" class="mt-1 text-xs text-red-600">
                {{ errors?.start_date }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">End Date</label>
            <input
                v-model="form.end_date"
                type="datetime-local"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                :aria-invalid="!!errors?.end_date"
            />
            <p v-if="errors?.end_date" class="mt-1 text-xs text-red-600">
                {{ errors?.end_date }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Sale Start</label>
            <input
                v-model="form.sale_start"
                type="datetime-local"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                :aria-invalid="!!errors?.sale_start"
            />
            <p v-if="errors?.sale_start" class="mt-1 text-xs text-red-600">
                {{ errors?.sale_start }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Sale End</label>
            <input
                v-model="form.sale_end"
                type="datetime-local"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                :aria-invalid="!!errors?.sale_end"
            />
            <p v-if="errors?.sale_end" class="mt-1 text-xs text-red-600">
                {{ errors?.sale_end }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Location</label>
            <input
                v-model="form.location"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Image URL</label>
            <input
                v-model="form.image_url"
                type="url"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import type { EventInput } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    modelValue: EventInput;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: EventInput): void;
}>();

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});
</script>
