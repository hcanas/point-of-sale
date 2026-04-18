<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import FormHelper from '@/components/forms/FormHelper.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FormNumberInput from '@/components/forms/FormNumberInput.vue';
import FormTextarea from '@/components/forms/FormTextarea.vue';
import Modal from '@/components/ui/Modal.vue';
import { store as stockMovementStore } from '@/routes/products/stock-movements';
import { useForm } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    productId: number;
    productName: string;
    currentStock: number;
}>();

const emit = defineEmits<{
    close: [];
}>();

type AdjustmentType = 'in' | 'out';

const adjustmentType = ref<AdjustmentType>('in');
const inputQuantity = ref(0);

const form = useForm({
    quantity: 0,
    notes: '',
});

const projectedStock = computed(() => {
    const signedQuantity = adjustmentType.value === 'in' ? inputQuantity.value : -inputQuantity.value;
    return props.currentStock + signedQuantity;
});

const resetForm = () => {
    adjustmentType.value = 'in';
    inputQuantity.value = 0;
    form.quantity = 0;
    form.notes = '';
    form.clearErrors();
};

watch(
    () => props.show,
    (isShown, wasShown) => {
        if (!isShown && wasShown) {
            resetForm();
        }
    },
);

const handleSubmit = () => {
    form.clearErrors();

    const finalQuantity = adjustmentType.value === 'in' ? inputQuantity.value : -inputQuantity.value;
    form.quantity = finalQuantity;

    form.post(stockMovementStore.url(props.productId), {
        onSuccess: () => {
            emit('close');
            resetForm();
        },
    });
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
                        'flex flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-medium transition-colors outline-none focus-visible:ring-success',
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
                        'flex flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-medium transition-colors outline-none focus-visible:ring-danger',
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
                <FormError :message="form.errors.quantity" />
            </div>

            <div>
                <FormLabel for="notes">Notes</FormLabel>
                <FormTextarea id="notes" v-model="form.notes" variant="canvas" placeholder="Reason for adjustment..." :rows="3" />
                <FormError :message="form.errors.notes" />
            </div>
        </form>

        <template #footer>
            <BtnSecondary type="button" @click="close" :disabled="form.processing">Cancel</BtnSecondary>
            <BtnPrimary type="submit" @click="handleSubmit" :disabled="form.processing || projectedStock < 0">
                {{ form.processing ? 'Adjusting...' : 'Adjust Stock' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
