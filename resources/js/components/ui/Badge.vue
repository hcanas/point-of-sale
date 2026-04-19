<script setup lang="ts">
import { computed, useSlots } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'success' | 'danger' | 'warning' | 'info' | 'primary' | 'secondary' | 'red' | 'blue' | 'emerald' | 'purple' | 'amber';
        size?: 'sm' | 'md' | 'lg';
        dot?: boolean;
        capitalize?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        dot: false,
        capitalize: true,
    }
);

const variantClasses = computed(() => {
    const classes = {
        success: 'bg-success/10 text-success dark:bg-success/20 dark:text-success-400',
        danger: 'bg-danger/10 text-danger dark:bg-danger/20 dark:text-danger-400',
        warning: 'bg-warning/10 text-warning dark:bg-warning/20 dark:text-warning-400',
        info: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        primary: 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-400',
        secondary: 'bg-canvas text-foreground-soft',
        red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        amber: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return classes[props.variant] || classes.primary;
});

const formattedText = computed(() => {
    const text = (useSlots().default?.()[0]?.children as string) || '';
    if (props.capitalize) {
        return text.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    }
    return text.replace(/_/g, ' ');
});

const sizeClasses = computed(() => {
    const classes = {
        sm: 'px-1.5 py-0.5 text-[10px]',
        md: 'px-2.5 py-1 text-xs',
        lg: 'px-3 py-1.5 text-sm',
    };
    return classes[props.size] || classes.md;
});

const dotColorClasses = computed(() => {
    const classes = {
        success: 'bg-success',
        danger: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-blue-500',
        primary: 'bg-primary-500',
        secondary: 'bg-foreground-soft',
        red: 'bg-red-500',
        blue: 'bg-blue-500',
        emerald: 'bg-emerald-500',
        purple: 'bg-purple-500',
        amber: 'bg-amber-500',
    };
    return classes[props.variant] || classes.primary;
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-full font-medium',
            variantClasses,
            sizeClasses,
        ]"
    >
        <span v-if="dot" :class="['h-1.5 w-1.5 rounded-full', dotColorClasses]"></span>
        {{ formattedText }}
    </span>
</template>
