<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    type?: 'button' | 'submit';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    disabled: false,
});

const emit = defineEmits<{
    click: [];
}>();

const classes = computed(() => ({
    'inline-flex h-9 cursor-pointer items-center gap-2 rounded-md border border-primary-600 bg-transparent px-4 text-sm font-medium text-primary-600': true,
    'hover:bg-primary-50': true,
    'dark:hover:bg-primary-900/20': true,
    'cursor-not-allowed opacity-50': props.disabled,
}));
</script>

<template>
    <button :type="type" :disabled="disabled" :class="classes" @click="emit('click')">
        <slot />
    </button>
</template>
