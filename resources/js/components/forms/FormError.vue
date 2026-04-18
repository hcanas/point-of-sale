<script setup lang="ts">
interface Props {
    message?: string;
}

defineProps<Props>();
</script>

<template>
    <p v-if="message" class="mt-1 text-sm text-danger">
        {{ message }}
    </p>
</template>
