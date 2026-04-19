<script setup lang="ts">
interface Props {
    type?: 'button' | 'submit';
    disabled?: boolean;
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
        class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md bg-danger px-4 text-sm font-medium text-white hover:bg-danger/90 focus-visible:ring-danger disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
