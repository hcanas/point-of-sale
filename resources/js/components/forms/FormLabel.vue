<script setup lang="ts">
interface Props {
    for?: string;
    class?: string;
    required?: boolean;
}

defineProps<Props>();
</script>

<template>
    <label :for="for" :class="['block text-sm font-medium text-foreground-soft', $props.class]">
        <slot />
        <span v-if="required" class="ml-1 text-red-500">*</span>
    </label>
</template>
