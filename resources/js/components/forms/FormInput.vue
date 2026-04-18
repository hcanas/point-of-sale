<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
    type?: string;
    id?: string;
    placeholder?: string;
    error?: boolean;
    variant?: 'canvas' | 'surface';
    keybind?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    variant: 'canvas',
});

const model = defineModel<string | null>();
const inputRef = ref<HTMLInputElement | null>(null);

const inputClasses = computed(() => [
    'block h-9 w-full rounded-md border border-divider px-3 text-sm text-foreground',
    props.variant === 'canvas' ? 'bg-canvas' : 'bg-surface',
    props.error && 'border-danger',
]);

const displayPlaceholder = computed(() => {
    if (!props.keybind) return props.placeholder;
    return `${props.placeholder} (${props.keybind})`;
});

defineExpose({
    focus: () => inputRef.value?.focus(),
});
</script>

<template>
    <input ref="inputRef" :id="id" :type="type" :placeholder="displayPlaceholder" v-model="model" :class="inputClasses" />
</template>
