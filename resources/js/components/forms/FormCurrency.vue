<script setup lang="ts">
import { computed, ref, watch } from 'vue';

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
    step: '0.01',
});

const inputClasses = computed(() => [
    'block w-full rounded-md border border-divider px-3 py-2 text-sm text-foreground outline-none focus-visible:ring-2 focus-visible:ring-primary-600',
    props.variant === 'canvas' ? 'bg-canvas' : 'bg-surface',
    props.error && 'border-danger',
]);

const inputRef = ref<HTMLInputElement | null>(null);
const inputValue = ref('');

// Sync inputValue from model (when not focused)
watch(
    () => model.value,
    (newValue) => {
        if (document.activeElement !== inputRef.value) {
            const num = typeof newValue === 'string' ? parseFloat(newValue) : newValue;
            inputValue.value = num !== undefined && !isNaN(num) ? num.toFixed(2) : '';
        }
    },
    { immediate: true },
);

function handleBlur() {
    const num = parseFloat(inputValue.value);
    if (!isNaN(num)) {
        const formatted = num.toFixed(2);
        inputValue.value = formatted;
        model.value = formatted;
    } else {
        inputValue.value = '';
        model.value = '';
    }
}

function handleFocus() {
    inputValue.value = (model.value as string) || '';
}

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
        v-model="inputValue"
        type="number"
        :min="min"
        :max="max"
        :step="step"
        :required="required"
        :placeholder="placeholder || '0.00'"
        :class="inputClasses"
        @blur="handleBlur"
        @focus="handleFocus"
    />
</template>
