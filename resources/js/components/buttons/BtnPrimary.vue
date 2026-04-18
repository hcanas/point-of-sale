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
    'inline-flex h-9 cursor-pointer items-center gap-2 rounded-md bg-primary-600 px-4 text-sm font-medium text-white': true,
    'pr-2': !!props.keybind,
    'hover:bg-primary-700': true,
    'cursor-not-allowed opacity-50': props.disabled,
}));
</script>

<template>
    <button :type="type" :disabled="disabled" :class="classes" @click="emit('click')">
        <slot />
        <kbd v-if="keybind" class="ml-2 rounded border border-white/20 bg-white/10 px-1.5 py-0.5 text-xs">
            {{ keybind }}
        </kbd>
    </button>
</template>
