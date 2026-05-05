<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import FormHelper from '@/components/forms/FormHelper.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FormNumberInput from '@/components/forms/FormNumberInput.vue';
import FormTextarea from '@/components/forms/FormTextarea.vue';
import Modal from '@/components/ui/Modal.vue';
import { store as stockMovementStore } from '@/routes/api/products/stock-movements';
import type { Product } from '@/types/inventory';
import axios from 'axios';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    productId: number;
    productName: string;
    currentStock: number;
}>();

const emit = defineEmits<{
    close: [product?: Product];
}>();

type AdjustmentType = 'in' | 'out';

const adjustmentType = ref<AdjustmentType>('in');
const inputQuantity = ref(0);

const form = reactive({
    quantity: 0,
    notes: '',
});

const errors = ref<Record<string, string>>({});
const isProcessing = ref(false);

const projectedStock = computed(() => {
    const signedQuantity = adjustmentType.value === 'in' ? inputQuantity.value : -inputQuantity.value;
    return props.currentStock + signedQuantity;
});

const resetForm = () => {
    adjustmentType.value = 'in';
    inputQuantity.value = 0;
    form.quantity = 0;
    form.notes = '';
    errors.value = {};
};

watch(
    () => props.show,
    (isShown, wasShown) => {
        if (!isShown && wasShown) {
            resetForm();
        }
    },
);

const handleSubmit = async () => {
    errors.value = {};
    isProcessing.value = true;

    const finalQuantity = adjustmentType.value === 'in' ? inputQuantity.value : -inputQuantity.value;
    form.quantity = finalQuantity;

    try {
        const response = await axios.post(stockMovementStore.url(props.productId), form);
        emit('close', response.data as Product);
        resetForm();
    } catch (error: any) {
        if (error.response?.status === 422) {
            const errorData = error.response.data.errors;
            for (const [field, messages] of Object.entries(errorData)) {
                errors.value[field] = (messages as string[])[0];
            }
        } else if (error.response?.status === 422 || error.response?.data?.message) {
            errors.value.quantity = error.response.data.message;
        }
    } finally {
        isProcessing.value = false;
    }
};

const close = () => emit('close');
</script>

<template>
    <Modal :show="show" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">Adjust Stock</h3>
                <p class="text-sm text-foreground-soft">
                    Adjusting stock for <strong class="text-foreground">"{{ productName }}"</strong>
                </p>
            </div>
        </template>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="rounded-lg bg-canvas p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-foreground-soft">Current Stock</span>
                    <span class="text-lg font-semibold text-foreground">{{ currentStock }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between border-t border-divider pt-2">
                    <span class="text-sm text-foreground-soft">After Adjustment</span>
                    <span class="text-lg font-semibold" :class="projectedStock < 0 ? 'text-danger' : 'text-success'">
                        {{ projectedStock }}
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="button"
                    @click="adjustmentType = 'in'"
                    :class="[
                        'flex flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-success focus-visible:ring-offset-2',
                        adjustmentType === 'in'
                            ? 'border-success bg-success/10 text-success'
                            : 'border-divider bg-canvas text-foreground-soft hover:text-foreground',
                    ]"
                >
                    <ArrowUp class="h-4 w-4" />
                    Stock In
                </button>
                <button
                    type="button"
                    @click="adjustmentType = 'out'"
                    :class="[
                        'flex flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-danger',
                        adjustmentType === 'out'
                            ? 'border-danger bg-danger/10 text-danger'
                            : 'border-divider bg-canvas text-foreground-soft hover:text-foreground',
                    ]"
                >
                    <ArrowDown class="h-4 w-4" />
                    Stock Out
                </button>
            </div>

            <div>
                <FormLabel for="quantity" required>Quantity</FormLabel>
                <FormNumberInput id="quantity" v-model="inputQuantity" :step="1" :min="1" variant="canvas" required />
                <FormHelper> Enter the quantity to {{ adjustmentType === 'in' ? 'add to' : 'remove from' }} stock </FormHelper>
                <FormError :message="errors.quantity" />
            </div>

            <div>
                <FormLabel for="notes">Notes</FormLabel>
                <FormTextarea id="notes" v-model="form.notes" variant="canvas" placeholder="Reason for adjustment..." :rows="3" />
                <FormError :message="errors.notes" />
            </div>
        </form>

        <template #footer>
            <BtnSecondary type="button" @click="close" :disabled="isProcessing">Cancel</BtnSecondary>
            <BtnPrimary type="submit" @click="handleSubmit" :disabled="isProcessing || projectedStock < 0">
                {{ isProcessing ? 'Adjusting...' : 'Adjust Stock' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
