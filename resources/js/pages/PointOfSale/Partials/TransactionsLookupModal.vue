<script setup lang="ts">
import FormInput from '@/components/forms/FormInput.vue';
import Modal from '@/components/ui/Modal.vue';
import { useFormatter } from '@/composables/useFormatter';
import type { Purchase } from '@/types/purchase';
import type { Payment, Sale } from '@/types/sale';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

type TransactionType = 'sales' | 'purchases' | 'payments';

interface TransactionTab {
    id: TransactionType;
    label: string;
}

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const { formatCurrency, formatDateTime } = useFormatter();

const tabs: TransactionTab[] = [
    { id: 'sales', label: 'Sales' },
    { id: 'purchases', label: 'Purchases' },
    { id: 'payments', label: 'Payments' },
];

const activeTab = ref<TransactionType>('sales');
const search = ref('');
const isLoading = ref(false);

const sales = ref<Sale[]>([]);
const purchases = ref<Purchase[]>([]);
const payments = ref<Payment[]>([]);

const loadTransactions = async () => {
    isLoading.value = true;
    try {
        const params = search.value ? { search: search.value, limit: 5 } : { limit: 5 };

        if (activeTab.value === 'sales') {
            const response = await axios.get('/api/sales', { params });
            sales.value = response.data.data || response.data || [];
        } else if (activeTab.value === 'purchases') {
            const response = await axios.get('/api/purchases', { params });
            purchases.value = response.data.data || response.data || [];
        } else if (activeTab.value === 'payments') {
            const response = await axios.get('/api/payments', { params });
            payments.value = response.data.data || response.data || [];
        }
    } catch (error) {
        console.error('Failed to load transactions:', error);
    } finally {
        isLoading.value = false;
    }
};

const debouncedSearch = useDebounceFn(() => {
    loadTransactions();
}, 300);

watch(
    [activeTab, search],
    () => {
        debouncedSearch();
    },
    { immediate: true },
);

const hasTransactions = computed(() => {
    if (activeTab.value === 'sales') return sales.value.length > 0;
    if (activeTab.value === 'purchases') return purchases.value.length > 0;
    return payments.value.length > 0;
});

const getTransactionTitle = (transaction: Sale | Purchase | Payment): string => {
    if (activeTab.value === 'sales') {
        const sale = transaction as Sale;
        return `Sale #${sale.reference_number}`;
    }
    if (activeTab.value === 'purchases') {
        const purchase = transaction as Purchase;
        return `Purchase #${purchase.reference_number || purchase.id}`;
    }
    const payment = transaction as Payment;
    return `Payment #${payment.id}`;
};

const getTransactionSubtitle = (transaction: Sale | Purchase | Payment): string => {
    if (activeTab.value === 'sales') {
        const sale = transaction as Sale;
        return sale.member_name || 'Walk-in Customer';
    }
    if (activeTab.value === 'purchases') {
        const purchase = transaction as Purchase;
        return purchase.vendor_name || 'Unknown Vendor';
    }
    const payment = transaction as Payment;
    if (payment.sale_id) {
        return `Payment for Sale #${payment.sale_id}`;
    }
    return payment.type === 'cash' ? 'Cash Payment' : 'Credit Payment';
};

const getTransactionAmount = (transaction: Sale | Purchase | Payment): number => {
    if (activeTab.value === 'sales') {
        return (transaction as Sale).total_amount;
    }
    if (activeTab.value === 'purchases') {
        return (transaction as Purchase).total_amount;
    }
    return (transaction as Payment).amount;
};

const getTransactionDate = (transaction: Sale | Purchase | Payment): string => {
    return formatDateTime(transaction.created_at);
};

const currentTransactions = computed(() => {
    if (activeTab.value === 'sales') return sales.value;
    if (activeTab.value === 'purchases') return purchases.value;
    return payments.value;
});
</script>

<template>
    <Modal :show="show" title="Transactions" @close="emit('close')">
        <div class="space-y-4">
            <!-- Search -->
            <FormInput v-model="search" placeholder="Search transactions..." variant="canvas" />

            <!-- Tabs -->
            <div class="flex gap-1 rounded-lg bg-canvas p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    class="flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === tab.id ? 'bg-surface text-foreground shadow-sm' : 'text-foreground-soft hover:text-foreground'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="py-8 text-center text-foreground-soft">Loading...</div>

            <!-- Empty State -->
            <div v-else-if="!hasTransactions" class="py-8 text-center text-foreground-soft">
                {{ search ? 'No transactions found matching your search.' : 'No recent transactions.' }}
            </div>

            <!-- Transactions List -->
            <div v-else class="space-y-2">
                <div
                    v-for="transaction in currentTransactions"
                    :key="transaction.id"
                    class="flex items-center justify-between rounded-md border border-divider bg-canvas p-3"
                >
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium text-foreground">
                            {{ getTransactionTitle(transaction) }}
                        </div>
                        <div class="truncate text-sm text-foreground-soft">
                            {{ getTransactionSubtitle(transaction) }}
                        </div>
                        <div class="text-xs text-foreground-muted">
                            {{ getTransactionDate(transaction) }}
                        </div>
                    </div>
                    <div class="ml-4 text-right">
                        <div class="font-semibold text-foreground">
                            {{ formatCurrency(getTransactionAmount(transaction)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
