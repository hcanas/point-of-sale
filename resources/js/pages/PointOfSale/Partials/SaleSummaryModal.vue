<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import Modal from '@/components/ui/Modal.vue';
import { useFormatter } from '@/composables/useFormatter';

interface Props {
    show: boolean;
    sale: any;
}

const props = defineProps<Props>();

const { formatCurrency, formatDateTime } = useFormatter();

const emit = defineEmits<{
    close: [];
}>();

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <template #header>
            <div class="text-center">
                <h2 class="text-xl font-bold text-foreground">RECEIPT</h2>
                <p class="mt-1 text-sm text-foreground-soft">{{ sale?.reference_number }}</p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-foreground-soft">Date</span>
                    <span class="font-medium text-foreground">{{ formatDateTime(sale?.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-foreground-soft">Member</span>
                    <span class="font-medium text-foreground">{{ sale?.member_name }}</span>
                </div>
            </div>

            <div class="border-t border-divider"></div>

            <div class="space-y-2">
                <div v-for="item in sale?.items" :key="item.id" class="flex justify-between text-sm">
                    <div class="flex-1">
                        <span class="font-medium text-foreground">{{ item.product_name }}</span>
                        <div class="text-foreground-soft">{{ item.quantity }} x {{ formatCurrency(item.unit_price) }}</div>
                    </div>
                    <span class="font-medium text-foreground">{{ formatCurrency(item.subtotal) }}</span>
                </div>
            </div>

            <div class="border-t border-divider"></div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-foreground-soft">Total</span>
                    <span class="text-lg font-bold text-foreground">{{ formatCurrency(sale?.total_amount || 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-foreground-soft">Amount Tendered</span>
                    <span class="font-medium text-foreground">{{ formatCurrency(sale?.amount_tendered || 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-foreground-soft">Change</span>
                    <span class="font-bold text-foreground">{{ formatCurrency(sale?.change_given || 0) }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <BtnSecondary @click="emit('close')">Close</BtnSecondary>
            <BtnPrimary @click="printReceipt">Print Receipt</BtnPrimary>
        </template>
    </Modal>
</template>
