<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <div
            class="rounded-lg bg-surface p-6 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
        >
            <h1 class="text-2xl font-semibold text-foreground">Dashboard</h1>
            <p class="mt-2 text-foreground-soft">Welcome to the POS system. Select an option from the menu above.</p>
        </div>
    </AuthenticatedLayout>
</template>
