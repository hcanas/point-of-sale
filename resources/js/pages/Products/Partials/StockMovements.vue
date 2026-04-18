<script setup lang="ts">
import FormInput from '@/components/forms/FormInput.vue';
import DataTable from '@/components/tables/DataTable.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useQueryStrings } from '@/composables/useQueryStrings';
import type { StockMovement } from '@/types/inventory';
import type { PaginatedData } from '@/types/pagination';
import { ArrowDown, ArrowUp, Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    movements: PaginatedData<StockMovement>;
}>();

const { formatDateTime } = useFormatter();
const { get, set } = useQueryStrings();

const search = computed({
    get: () => get('search') ?? '',
    set: (val) => set('search', val || undefined),
});

const searchInputRef = ref<InstanceType<typeof FormInput> | null>(null);

const focusSearch = () => {
    searchInputRef.value?.focus();
};

defineExpose({ focusSearch });

onMounted(() => {
    focusSearch();
});

const getQuantityDisplay = (movement: StockMovement) => {
    const isPositive = movement.quantity > 0;
    return {
        text: `${isPositive ? '+' : ''}${movement.quantity}`,
        class: isPositive ? 'text-success' : 'text-danger',
        icon: isPositive ? ArrowUp : ArrowDown,
    };
};
</script>

<template>
    <div
        class="rounded-lg bg-surface p-6 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
    >
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground">Stock Movements</h2>
            <div class="w-72">
                <FormInput ref="searchInputRef" v-model="search" placeholder="Search movements... (Ctrl+F)">
                    <template #left>
                        <Search class="h-4 w-4 text-foreground-muted" />
                    </template>
                </FormInput>
            </div>
        </div>

        <DataTable
            :columns="[
                { key: 'created_at', label: 'Date', width: 'fill' },
                { key: 'reference_type', label: 'Type' },
                { key: 'quantity', label: 'Quantity', align: 'right' },
                { key: 'after_quantity', label: 'After', align: 'center' },
                { key: 'creator', label: 'By' },
            ]"
            :data="movements"
        >
            <template #cell-created_at="{ row }">
                <span class="text-sm text-foreground">{{ formatDateTime(row.created_at) }}</span>
            </template>
            <template #cell-reference_type="{ value }">
                <span v-if="value" class="rounded-full bg-canvas px-2 py-1 text-xs font-medium text-foreground-soft">{{ value }}</span>
                <span v-else class="text-foreground-muted">—</span>
            </template>
            <template #cell-quantity="{ row }">
                <template v-for="display in [getQuantityDisplay(row)]" :key="row.id">
                    <span :class="['font-semibold', display.class]">
                        <component :is="display.icon" class="mr-1 inline h-4 w-4" />
                        {{ display.text }}
                    </span>
                </template>
            </template>
            <template #cell-after_quantity="{ value }">
                <span class="font-medium text-foreground">{{ value }}</span>
            </template>
            <template #cell-creator="{ row }">
                <span v-if="row.creator" class="text-sm text-foreground">{{ row.creator.first_name }} {{ row.creator.last_name }}</span>
                <span v-else class="text-foreground-muted">—</span>
            </template>
        </DataTable>
    </div>
</template>
