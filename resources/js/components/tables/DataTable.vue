<script setup lang="ts">
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import type { PaginationData } from '@/types/pagination';
import { Link, router } from '@inertiajs/vue3';
import { ArrowUpDown, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    width?: 'auto' | 'fill';
}

interface Props {
    columns: Column[];
    data: PaginationData;
    emptyText?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    emptyText: 'No results found',
    loading: false,
});

const { get, set, navigate } = useQueryStrings();

const sortKey = computed(() => get('sort') ?? '');
const sortDirection = computed(() => (get('sort_direction') as 'asc' | 'desc') ?? 'asc');

const prevPageUrl = computed(() => {
    const prevLink = props.data.links.find((link) => link.label === '&laquo; Previous');
    return prevLink?.url ?? null;
});

const nextPageUrl = computed(() => {
    const nextLink = props.data.links.find((link) => link.label === 'Next &raquo;');
    return nextLink?.url ?? null;
});

useKeybinds([
    { key: 'ArrowLeft', ctrl: true, handler: () => prevPageUrl.value && router.visit(prevPageUrl.value) },
    { key: 'ArrowRight', ctrl: true, handler: () => nextPageUrl.value && router.visit(nextPageUrl.value) },
]);

const handleSort = (column: Column) => {
    if (!column.sortable) return;
    if (sortKey.value === column.key) {
        if (sortDirection.value === 'asc') {
            set('sort_direction', 'desc');
        } else {
            set('sort', undefined);
            set('sort_direction', undefined);
        }
    } else {
        set('sort', column.key);
        set('sort_direction', 'asc');
    }
};

const rowRefs = ref<HTMLTableRowElement[]>([]);
const focusedRowIndex = ref(-1);

const handleKeydown = (e: KeyboardEvent) => {
    if (!props.data.data.length) return;

    const active = document.activeElement;
    const isInput = active instanceof HTMLInputElement || active instanceof HTMLTextAreaElement || active instanceof HTMLSelectElement;

    if (isInput && e.key !== 'ArrowDown') {
        return;
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (isInput) {
                focusedRowIndex.value = 0;
            } else {
                focusedRowIndex.value = Math.min(focusedRowIndex.value + 1, props.data.data.length - 1);
            }
            rowRefs.value[focusedRowIndex.value]?.focus();
            break;
        case 'ArrowUp':
            e.preventDefault();
            focusedRowIndex.value = Math.max(focusedRowIndex.value - 1, 0);
            rowRefs.value[focusedRowIndex.value]?.focus();
            break;
        case 'Enter':
            if (focusedRowIndex.value >= 0) {
                const focusedRow = rowRefs.value[focusedRowIndex.value];
                const link = focusedRow?.querySelector('a');
                if (link instanceof HTMLElement) {
                    e.preventDefault();
                    link.click();
                }
            }
            break;
    }
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

watch(
    () => props.data.data,
    () => {
        focusedRowIndex.value = -1;
        rowRefs.value = [];
    },
    { deep: true },
);

const getAlignmentClass = (align?: string) => {
    switch (align) {
        case 'center':
            return 'text-center';
        case 'right':
            return 'text-right';
        default:
            return 'text-left';
    }
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="border-b border-divider">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :class="[
                            'px-4 py-3 text-left text-sm font-semibold text-foreground',
                            column.align === 'center' && 'text-center',
                            column.align === 'right' && 'text-right',
                            column.sortable && 'cursor-pointer select-none hover:text-primary-600',
                            column.width === 'fill' ? 'w-full' : 'whitespace-nowrap',
                        ]"
                        @click="handleSort(column)"
                    >
                        <div
                            class="flex items-center gap-1"
                            :class="{ 'justify-center': column.align === 'center', 'justify-end': column.align === 'right' }"
                        >
                            {{ column.label }}
                            <template v-if="column.sortable">
                                <ArrowUpDown v-if="sortKey !== column.key" class="h-4 w-4 text-foreground-muted opacity-40" />
                                <ChevronUp v-else-if="sortDirection === 'asc'" class="h-4 w-4 text-primary-600" />
                                <ChevronDown v-else class="h-4 w-4 text-primary-600" />
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider">
                <tr v-if="loading">
                    <td :colspan="columns.length" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-200 border-t-primary-600" />
                            <span class="text-sm text-foreground-soft">Loading data...</span>
                        </div>
                    </td>
                </tr>
                <tr v-else-if="data.data.length === 0">
                    <td :colspan="columns.length" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="rounded-full bg-slate-100 p-3 dark:bg-slate-800">
                                <svg class="h-6 w-6 text-foreground-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414a1 1 0 00-.707-.293H4"
                                    />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-foreground">No data available</p>
                            <p class="text-xs text-foreground-muted">{{ emptyText }}</p>
                        </div>
                    </td>
                </tr>
                <tr
                    v-for="(row, index) in data.data"
                    :key="row.id"
                    :ref="
                        (el) => {
                            if (el) rowRefs[index] = el as HTMLTableRowElement;
                        }
                    "
                    :tabindex="focusedRowIndex === index ? 0 : -1"
                    :class="[
                        'transition-colors outline-none',
                        focusedRowIndex === index && 'bg-primary-50/60 ring-1 ring-primary-600/30 ring-inset dark:bg-primary-900/20',
                    ]"
                    @mouseenter="focusedRowIndex = index"
                    @focus="focusedRowIndex = index"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-4 py-3 text-sm"
                        :class="[
                            getAlignmentClass(column.align),
                            column.align === 'right' && 'tabular-nums',
                            column.width === 'fill' ? 'w-full' : 'whitespace-nowrap',
                        ]"
                    >
                        <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                            <span class="text-foreground">{{ row[column.key] }}</span>
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="data.links.length > 3" class="mt-4 flex items-center justify-between border-t border-divider pt-4">
        <slot
            name="pagination"
            :links="data.links"
            :from="data.from"
            :to="data.to"
            :total="data.total"
            :per-page="data.per_page"
            :current-page="data.current_page"
        >
            <p class="text-sm text-foreground-soft">
                Showing <span class="font-medium text-foreground">{{ data.from || 0 }}</span> to
                <span class="font-medium text-foreground">{{ data.to || 0 }}</span> of
                <span class="font-medium text-foreground">{{ data.total }}</span> results
            </p>
            <div class="flex items-center gap-0.5">
                <template v-for="link in data.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url as string"
                        class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-md px-2 text-sm font-medium transition-colors"
                        :class="link.active ? 'bg-primary-600 text-white' : 'text-foreground-soft hover:bg-hover hover:text-foreground'"
                        v-html="link.label"
                    />
                    <span
                        v-if="!link.url"
                        class="pointer-events-none inline-flex h-8 min-w-[2rem] items-center justify-center rounded-md px-2 text-sm font-medium opacity-40 transition-colors"
                        v-html="link.label"
                    />
                </template>
            </div>
        </slot>
    </div>
</template>
