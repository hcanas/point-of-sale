<script setup lang="ts">
import { computed, ref } from 'vue';

const model = defineModel<string | number>();

interface Props {
    id?: string;
    min?: number;
    max?: number;
    step?: number | string;
    required?: boolean;
    variant?: 'default' | 'canvas';
    error?: boolean;
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    error: false,
});

const inputClasses = computed(() => [
    'block w-full rounded-md border border-divider px-3 py-2 text-sm text-foreground [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none',
    props.variant === 'canvas' ? 'bg-canvas' : 'bg-surface',
    props.error && 'border-danger',
]);

const inputRef = ref<HTMLInputElement | null>(null);

function focus() {
    inputRef.value?.focus();
}

defineExpose({ focus });
</script>

<template>
    <input
        :id="id"
        ref="inputRef"
        v-model="model"
        type="number"
        :min="min"
        :max="max"
        :step="step"
        :required="required"
        :placeholder="placeholder"
        :class="inputClasses"
    />
</template>
