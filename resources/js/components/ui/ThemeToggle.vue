<script setup lang="ts">
import { Sun, Moon } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

interface Props {
    variant?: 'default' | 'compact';
}

withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button
        @click="toggleTheme"
        :class="[
            'group relative flex items-center rounded-md text-foreground-soft transition-colors hover:text-foreground outline-none',
            variant === 'default'
                ? 'w-full justify-between border border-divider bg-canvas px-3 py-2'
                : 'h-9 w-9 items-center justify-center hover:bg-hover',
        ]"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <span v-if="variant === 'default'" class="text-sm font-medium">Theme</span>
        <span
            v-if="variant === 'default'"
            :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                isDark ? 'bg-primary-600' : 'bg-slate-200',
            ]"
        >
            <span
                :class="[
                    'absolute left-0.5 inline-flex h-5 w-5 transform items-center justify-center rounded-full bg-white transition-transform duration-200',
                    isDark ? 'translate-x-5' : 'translate-x-0',
                ]"
            >
                <Sun v-if="!isDark" class="h-3 w-3" />
                <Moon v-else class="h-3 w-3 text-slate-700" />
            </span>
        </span>

        <template v-if="variant === 'compact'">
            <Sun v-if="isDark" class="h-5 w-5" />
            <Moon v-else class="h-5 w-5" />
        </template>
    </button>
</template>
