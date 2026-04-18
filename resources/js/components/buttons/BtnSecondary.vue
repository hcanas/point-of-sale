<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    type?: 'button' | 'submit';
    disabled?: boolean;
    keybind?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    disabled: false,
});

const emit = defineEmits<{
    click: [];
}>();

const classes = computed(() => ({
    'inline-flex h-9 cursor-pointer items-center gap-2 rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700': true,
    'hover:bg-gray-50': true,
    'dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700': true,
    'cursor-not-allowed opacity-50': props.disabled,
    'pr-2': !!props.keybind,
}));
</script>

<template>
    <button :type="type" :disabled="disabled" :class="classes" @click="emit('click')">
        <slot />
        <kbd v-if="keybind" class="ml-2 rounded border border-gray-300 bg-gray-100 px-1.5 py-0.5 text-xs dark:border-gray-600 dark:bg-gray-700">
            {{ keybind }}
        </kbd>
    </button>
</template>
