<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    href: string;
}

defineProps<Props>();
</script>

<template>
    <Link :href="href" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">
        <slot />
    </Link>
</template>
