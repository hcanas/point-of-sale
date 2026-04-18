<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';

interface Props {
    show: boolean;
    title?: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
    maxWidth: 'md',
});

const emit = defineEmits<{
    close: [];
}>();

const dialogRef = ref<HTMLDialogElement | null>(null);

const maxWidthClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
};

watch(
    () => props.show,
    async (show) => {
        await nextTick();
        const dialog = dialogRef.value;
        if (!dialog) return;

        if (show && !dialog.open) {
            dialog.showModal();
        } else if (!show && dialog.open) {
            dialog.close();
        }
    },
    { immediate: true }
);

const handleClose = () => {
    emit('close');
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key !== 'Escape') {
        e.stopPropagation();
    }
};
</script>

<template>
    <dialog
        ref="dialogRef"
        :class="[
            'relative m-auto w-full overflow-hidden rounded-lg bg-surface shadow-xl backdrop:bg-black/50 backdrop:backdrop-blur-sm',
            'open:animate-in open:fade-in open:zoom-in-95',
            maxWidthClasses[maxWidth],
        ]"
        @close="handleClose"
        @keydown="handleKeydown"
    >
        <div
            v-if="title || $slots.header"
            class="border-b border-divider px-6 py-4"
        >
            <slot name="header">
                <h3 class="text-lg font-semibold text-foreground">{{ title }}</h3>
            </slot>
        </div>

        <div class="max-h-[60vh] overflow-y-auto px-6 py-4 text-foreground">
            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="flex items-center justify-end gap-3 border-t border-divider px-6 py-4"
        >
            <slot name="footer" />
        </div>

        <button
            type="button"
            class="absolute right-4 top-4 rounded-md p-1 text-foreground-soft hover:bg-hover hover:text-foreground"
            @click="emit('close')"
        >
            <X class="h-5 w-5" />
        </button>
    </dialog>
</template>
