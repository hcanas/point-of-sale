<script setup lang="ts">
import FormInput from '@/components/forms/FormInput.vue';
import UserNameLink from '@/components/links/UserNameLink.vue';
import DataTable from '@/components/tables/DataTable.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useQueryStrings } from '@/composables/useQueryStrings';
import type { MemberLedger } from '@/types/member';
import type { PaginatedData } from '@/types/pagination';
import { ArrowDown, ArrowUp, Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    ledgers: PaginatedData<MemberLedger>;
    currentUrl?: string;
}>();

const { formatCurrency, formatDateTime } = useFormatter();
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

const getLedgerTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        purchase: 'Purchase',
        payment: 'Payment',
        adjustment: 'Adjustment',
        initial: 'Initial',
    };
    return labels[type] || type;
};

const getAmountDisplay = (ledger: MemberLedger) => {
    return formatCurrency(ledger.amount);
};

const getBalanceDisplay = (ledger: MemberLedger) => {
    const isPositive = ledger.balance_after >= 0;
    return {
        text: formatCurrency(ledger.balance_after),
        class: isPositive ? 'text-danger' : 'text-success',
        icon: isPositive ? ArrowUp : ArrowDown,
    };
};
</script>

<template>
    <div
        class="rounded-lg bg-surface p-6 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
    >
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground">Transaction History</h2>
            <div class="w-72">
                <FormInput ref="searchInputRef" v-model="search" placeholder="Search transactions... (Ctrl+F)">
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
                { key: 'amount', label: 'Amount', align: 'right' },
                { key: 'balance_after', label: 'Balance After', align: 'right' },
                { key: 'creator', label: 'By' },
            ]"
            :data="ledgers"
        >
            <template #cell-created_at="{ row }">
                <span class="text-sm text-foreground">{{ formatDateTime(row.created_at) }}</span>
            </template>
            <template #cell-reference_type="{ value }">
                <span v-if="value" class="rounded-full bg-canvas px-2 py-1 text-xs font-medium text-foreground-soft">{{
                    getLedgerTypeLabel(value)
                }}</span>
                <span v-else class="text-foreground-muted">—</span>
            </template>
            <template #cell-amount="{ row }">
                <span class="font-semibold text-foreground tabular-nums">{{ getAmountDisplay(row) }}</span>
            </template>
            <template #cell-balance_after="{ row }">
                <template v-for="display in [getBalanceDisplay(row)]" :key="row.id">
                    <span :class="['font-semibold tabular-nums', display.class]">
                        <component :is="display.icon" class="mr-1 inline h-4 w-4" />
                        {{ display.text }}
                    </span>
                </template>
            </template>
            <template #cell-creator="{ row }">
                <UserNameLink v-if="row.creator" :user="row.creator" :return-to="currentUrl" />
                <span v-else class="text-foreground-muted">—</span>
            </template>
        </DataTable>
    </div>
</template>
