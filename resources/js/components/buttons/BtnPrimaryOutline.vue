<script setup lang="ts">
interface Props {
    type?: 'button' | 'submit';
    disabled?: boolean;
    keybind?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    disabled: false,
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md border border-primary-600 bg-transparent px-4 text-sm font-medium text-primary-600 hover:bg-primary-50 focus-visible:ring-2 focus-visible:ring-primary-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-primary-900/20"
        :class="{ 'pr-2': keybind }"
    >
        <slot />
        <kbd v-if="keybind" class="ml-2">{{ keybind }}</kbd>
    </button>
</template>
