<script setup lang="ts">
interface Props {
    message?: string;
}

defineProps<Props>();
</script>

<template>
    <p
        v-if="message"
        class="rounded-md bg-red-50 p-3 text-sm text-danger dark:bg-red-900/20"
    >
        {{ message }}
    </p>
</template>
