<script setup lang="ts">
import ThemeToggle from '@/components/ui/ThemeToggle.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-canvas p-6">
        <div class="absolute top-4 right-4 w-40">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-md">
            <div
                class="overflow-hidden rounded-lg bg-surface shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
