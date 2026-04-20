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
    'block w-full rounded-md border border-divider px-3 py-2 text-sm text-foreground',
    props.variant === 'canvas' ? 'bg-canvas' : 'bg-surface',
    props.error && 'border-danger',
]);

const inputRef = ref<HTMLInputElement | null>(null);

function focus() {
    inputRef.value?.focus();
}

defineExpose({ focus });
</script>

<style scoped>
input::-webkit-inner-spin-button,
input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>

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
