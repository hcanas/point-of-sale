<script setup lang="ts">
interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-foreground">{{ title }}</h1>
        <div class="flex items-center gap-3">
            <slot />
        </div>
    </div>
</template>
