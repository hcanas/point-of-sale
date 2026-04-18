<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import FormHelper from '@/components/forms/FormHelper.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FormNumberInput from '@/components/forms/FormNumberInput.vue';
import Modal from '@/components/ui/Modal.vue';
import { store, update } from '@/routes/products';
import type { Product } from '@/types/inventory';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

type ProductFormData = Omit<Product, 'id' | 'stock' | 'created_at' | 'updated_at' | 'created_by' | 'updated_by'> & { id?: number };

const props = defineProps<{
    show: boolean;
    product?: ProductFormData;
}>();

const emit = defineEmits<{
    close: [];
}>();

const defaultFormValues: ProductFormData = {
    barcode: props.product?.barcode ?? null,
    name: props.product?.name ?? '',
    description: props.product?.description ?? null,
    stock_warning_level: props.product?.stock_warning_level ?? 10,
    price: props.product?.price ?? 0,
    is_active: props.product?.is_active ?? true,
};

const form = useForm<ProductFormData>({ ...defaultFormValues });

const isEditing = () => props.product?.id !== undefined;

const resetForm = () => {
    Object.assign(form, defaultFormValues);
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

    const data = {
        barcode: form.barcode,
        name: form.name,
        description: form.description || null,
        stock_warning_level: form.stock_warning_level,
        price: form.price,
        is_active: form.is_active,
    };

    const options = {
        onSuccess: () => {
            emit('close');
            if (!isEditing()) resetForm();
        },
    };

    if (isEditing() && props.product?.id) {
        form.put(update.url(props.product.id), { ...data, ...options });
    } else {
        form.post(store.url(), { ...data, ...options });
    }
};

const close = () => emit('close');
</script>

<template>
    <Modal :show="show" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">{{ isEditing() ? 'Edit Product' : 'Create Product' }}</h3>
                <p class="text-sm text-foreground-soft">
                    {{ isEditing() ? 'Update product details below' : 'Enter product information to add a new product' }}
                </p>
            </div>
        </template>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <FormLabel for="name" required>Product Name</FormLabel>
                <FormInput id="name" v-model="form.name" type="text" variant="canvas" required />
                <FormError :message="form.errors.name" />
            </div>

            <div>
                <FormLabel for="description">Description</FormLabel>
                <FormInput id="description" v-model="form.description" variant="canvas" />
                <FormError :message="form.errors.description" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <FormLabel for="barcode">Barcode</FormLabel>
                    <FormInput id="barcode" v-model="form.barcode" type="text" variant="canvas" />
                    <FormError :message="form.errors.barcode" />
                </div>

                <div>
                    <FormLabel for="price" required>Price</FormLabel>
                    <FormNumberInput id="price" v-model="form.price" :step="0.01" :min="0" variant="canvas" required />
                    <FormError :message="form.errors.price" />
                </div>
            </div>

            <div>
                <FormLabel for="stock_warning_level" required>Stock Warning Level</FormLabel>
                <FormNumberInput id="stock_warning_level" v-model="form.stock_warning_level" :min="0" variant="canvas" required />
                <FormHelper>Receive alerts when stock falls below this level</FormHelper>
                <FormError :message="form.errors.stock_warning_level" />
            </div>

            <div class="flex items-start gap-2">
                <input id="is_active" v-model="form.is_active" type="checkbox" class="mt-0.5 rounded border-gray-300 dark:border-gray-600" />
                <div class="flex flex-col">
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                    <FormHelper>Inactive products won't appear in sales</FormHelper>
                </div>
            </div>
        </form>

        <template #footer>
            <BtnSecondary type="button" @click="close" :disabled="form.processing">Cancel</BtnSecondary>
            <BtnPrimary type="submit" @click="handleSubmit" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : isEditing() ? 'Update' : 'Create' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
