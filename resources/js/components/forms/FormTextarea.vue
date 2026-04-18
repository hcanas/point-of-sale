<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
    id?: string;
    placeholder?: string;
    rows?: number;
    variant?: 'canvas' | 'surface';
    error?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    rows: 3,
    variant: 'canvas',
    error: false,
});

const model = defineModel<string | null>();
const textareaRef = ref<HTMLTextAreaElement | null>(null);

const textareaClasses = computed(() => [
    'block w-full rounded-md border border-divider px-3 py-2 text-sm text-foreground',
    props.variant === 'canvas' ? 'bg-canvas' : 'bg-surface',
    props.error && 'border-danger',
    'resize-y',
]);

defineExpose({
    focus: () => textareaRef.value?.focus(),
});
</script>

<template>
    <textarea :id="id" ref="textareaRef" :placeholder="placeholder" :rows="rows" v-model="model" :class="textareaClasses" />
</template>
