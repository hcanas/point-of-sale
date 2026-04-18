<script setup lang="ts">
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    message: string;
    type?: 'success' | 'error' | 'warning' | 'info';
    duration?: number;
}>();

const emit = defineEmits<{
    close: [];
}>();

const visible = ref(false);
const progressWidth = ref(100);

const icons = {
    success: CheckCircle,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info,
};

const icon = computed(() => icons[props.type ?? 'info']);

const iconClass = computed(() => {
    switch (props.type) {
        case 'success': return 'text-emerald-500';
        case 'error': return 'text-red-500';
        case 'warning': return 'text-amber-500';
        default: return 'text-blue-500';
    }
});

const borderClass = computed(() => {
    switch (props.type) {
        case 'success': return 'border-l-emerald-500';
        case 'error': return 'border-l-red-500';
        case 'warning': return 'border-l-amber-500';
        default: return 'border-l-blue-500';
    }
});

const progressBarClass = computed(() => {
    switch (props.type) {
        case 'success': return 'bg-emerald-500';
        case 'error': return 'bg-red-500';
        case 'warning': return 'bg-amber-500';
        default: return 'bg-blue-500';
    }
});

let timeoutId: ReturnType<typeof setTimeout> | null = null;
let rafId: number | null = null;

const dismiss = () => {
    visible.value = false;
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
    if (rafId) {
        cancelAnimationFrame(rafId);
        rafId = null;
    }
    setTimeout(() => emit('close'), 300);
};

const startProgress = () => {
    const duration = props.duration ?? 3000;
    const startTime = performance.now();

    const updateProgress = (currentTime: number) => {
        const elapsed = currentTime - startTime;
        const remaining = Math.max(0, duration - elapsed);
        progressWidth.value = (remaining / duration) * 100;

        if (remaining > 0) {
            rafId = requestAnimationFrame(updateProgress);
        }
    };

    rafId = requestAnimationFrame(updateProgress);
    timeoutId = setTimeout(dismiss, duration);
};

onMounted(() => {
    visible.value = true;
    const duration = props.duration ?? 3000;
    if (duration > 0) {
        setTimeout(startProgress, 300);
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transform transition-all duration-300 ease-out"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transform transition-all duration-300 ease-out"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <div
                v-if="visible"
                :class="[
                    'pointer-events-auto fixed right-4 top-4 z-50 flex w-80 flex-col overflow-hidden rounded-lg bg-white dark:bg-gray-800 p-4 shadow-lg',
                    'border-l-4',
                    borderClass,
                ]"
                role="alert"
            >
                <div class="flex items-start gap-3">
                    <component :is="icon" :class="['h-5 w-5 shrink-0', iconClass]" />
                    <p class="flex-1 text-sm text-gray-800 dark:text-gray-200">
                        {{ message }}
                    </p>
                    <button
                        @click="dismiss"
                        class="shrink-0 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                        aria-label="Close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div
                    v-if="(duration ?? 5000) > 0"
                    class="absolute right-0 bottom-0 left-0 h-1"
                    :class="progressBarClass"
                    :style="{ width: progressWidth + '%', transition: 'none' }"
                />
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
</style>
