<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface Props {
    href: string;
    icon?: Component;
    keybind?: string;
    active?: boolean;
}

const props = defineProps<Props>();
const page = usePage();

const isActive = props.active ?? page.url.startsWith(props.href);
</script>

<template>
    <Link
        :href="href"
        :class="[
            'flex items-center rounded-md px-3 py-3 text-sm font-medium transition-colors outline-none',
            isActive ? 'bg-primary-600 text-white shadow-sm' : 'text-foreground-soft hover:bg-hover hover:text-foreground',
        ]"
    >
        <component v-if="icon" :is="icon" class="h-5 w-5 shrink-0" />
        <span class="ml-3">
            <slot />
        </span>
        <kbd v-if="keybind" class="ml-auto rounded border border-divider bg-canvas px-1.5 py-0.5 text-xs text-foreground-soft">
            {{ keybind }}
        </kbd>
    </Link>
</template>
