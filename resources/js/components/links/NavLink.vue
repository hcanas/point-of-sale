<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface Props {
    href: string;
    icon?: Component;
    keybind?: string;
    active?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    active: false,
});
</script>

<template>
    <Link
        :href="href"
        :class="[
            'flex items-center justify-between rounded-md px-3 py-3 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-primary-600',
            active ? 'bg-primary-600 text-white shadow-sm' : 'text-foreground-soft hover:bg-hover hover:text-foreground',
        ]"
    >
        <span class="flex items-center">
            <component v-if="icon" :is="icon" class="h-5 w-5 shrink-0" />
            <span class="ml-3">
                <slot />
            </span>
        </span>
        <kbd v-if="keybind" class="ml-2">{{ keybind }}</kbd>
    </Link>
</template>
