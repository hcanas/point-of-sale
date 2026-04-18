<script setup lang="ts">
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <p :class="['text-xs text-foreground-soft', $props.class]">
        <slot />
    </p>
</template>
