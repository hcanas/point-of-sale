<script setup lang="ts">
import BtnDanger from '@/components/buttons/BtnDanger.vue';
import BtnDangerOutline from '@/components/buttons/BtnDangerOutline.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import { destroy } from '@/routes/products';
import type { ProductWithRelations } from '@/types/inventory';
import { router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';

const props = defineProps<{
    product: ProductWithRelations;
}>();

const showDeletePanel = ref(false);
const deleteConfirmation = ref('');
const deleteInputRef = ref<HTMLInputElement | null>(null);

const openDeletePanel = () => {
    showDeletePanel.value = true;
    deleteConfirmation.value = '';
    nextTick(() => deleteInputRef.value?.focus());
};

const closeDeletePanel = () => {
    showDeletePanel.value = false;
    deleteConfirmation.value = '';
};

const deleteProduct = () => {
    router.delete(destroy.url(props.product.id));
};
</script>

<template>
    <div class="rounded-lg border bg-surface" :class="product.deletable ? 'border-danger' : 'border-divider'">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <div class="rounded p-2" :class="product.deletable ? 'bg-danger/10' : 'bg-foreground-soft/10'">
                    <Trash2 class="h-5 w-5" :class="product.deletable ? 'text-danger' : 'text-foreground-soft'" />
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-foreground">Delete this product</h3>
                    <p class="mt-1 text-sm text-foreground-soft">
                        <template v-if="!product.deletable"> This product has related records and cannot be deleted. </template>
                        <template v-else>
                            Deletion is only allowed for products with no related records (stock movements, sales, or purchases). Products with
                            history cannot be deleted.
                        </template>
                    </p>
                    <BtnDangerOutline v-if="!showDeletePanel && product.deletable" class="mt-4" @click="openDeletePanel">
                        Delete this product
                    </BtnDangerOutline>
                </div>
            </div>

            <div v-if="showDeletePanel" class="border-border mt-6 border-t pt-6">
                <div class="rounded-lg border border-warning bg-warning/10 p-4">
                    <p class="text-sm">
                        <span class="font-medium text-warning">Warning:</span>
                        <span class="text-foreground-soft">
                            This will permanently delete
                            <strong class="text-foreground">"{{ product.name }}"</strong>. If any related records exist, deletion will be blocked.
                        </span>
                    </p>
                </div>

                <form @submit.prevent="deleteProduct" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground">
                            To confirm, type
                            <strong class="text-danger">"{{ product.name }}"</strong>
                            below:
                        </label>
                        <input
                            ref="deleteInputRef"
                            v-model="deleteConfirmation"
                            type="text"
                            class="mt-2 block w-full rounded-md border border-divider bg-canvas px-3 py-2 text-sm text-foreground placeholder:text-foreground-muted focus:ring-danger"
                            placeholder="Type product name to confirm"
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <BtnSecondary type="button" @click="closeDeletePanel">Cancel</BtnSecondary>
                        <BtnDanger type="submit" :disabled="deleteConfirmation !== product.name"> I understand, delete this product </BtnDanger>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
