<script setup lang="ts">
import BtnDanger from '@/components/buttons/BtnDanger.vue';
import BtnDangerOutline from '@/components/buttons/BtnDangerOutline.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import ReturnLink from '@/components/links/ReturnLink.vue';
import UserNameLink from '@/components/links/UserNameLink.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Toast from '@/components/ui/Toast.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import ProductFormModal from '@/pages/Products/Partials/ProductFormModal.vue';
import StockAdjustmentModal from '@/pages/Products/Partials/StockAdjustmentModal.vue';
import StockMovements from '@/pages/Products/Partials/StockMovements.vue';
import { destroy, index } from '@/routes/products';
import type { ProductWithRelations, StockMovement } from '@/types/inventory';
import type { PaginatedData } from '@/types/pagination';
import { router, usePage } from '@inertiajs/vue3';
import { AlertCircle, Package, Pencil, SlidersHorizontal, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps<{
    product: ProductWithRelations;
    movements: PaginatedData<StockMovement>;
}>();

const page = usePage();
const currentUrl = computed(() => page.url);
const flashMessage = computed(() => page.props.flash);
const showToast = ref(false);
const toastType = computed(() => {
    if (flashMessage.value?.success) return 'success';
    if (flashMessage.value?.error) return 'error';
    return 'info';
});
const toastMessage = computed(() => {
    return flashMessage.value?.success || flashMessage.value?.error || '';
});

watch(
    flashMessage,
    (newFlash) => {
        if (newFlash?.success || newFlash?.error) {
            showToast.value = true;
        }
    },
    { immediate: true },
);

const closeToast = () => {
    showToast.value = false;
};

const { getReturnUrl } = useQueryStrings();
const { formatCurrency, formatNumber, formatDateTime } = useFormatter();

const showModal = ref(false);
const showAdjustModal = ref(false);
const showDeletePanel = ref(false);
const deleteConfirmation = ref('');
const deleteInputRef = ref<HTMLInputElement | null>(null);
const stockMovementsRef = ref<InstanceType<typeof StockMovements> | null>(null);

const focusStockSearch = () => {
    stockMovementsRef.value?.focusSearch();
};

const openEditModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const openAdjustModal = () => {
    showAdjustModal.value = true;
};

const closeAdjustModal = () => {
    showAdjustModal.value = false;
};

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

useKeybinds([
    { key: 'j', ctrl: true, handler: openAdjustModal },
    { key: 'e', ctrl: true, handler: openEditModal },
    { key: 'f', ctrl: true, handler: focusStockSearch },
    { key: 'ArrowLeft', alt: true, handler: () => router.visit(getReturnUrl('return_to', index.url())) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <ReturnLink fallback="/products" />
            </div>

            <PageHeader :title="product.name">
                <BtnSecondary keybind="Ctrl+J" @click="openAdjustModal">
                    <SlidersHorizontal class="h-4 w-4" />
                    Adjust Stock
                </BtnSecondary>
                <BtnSecondary keybind="Ctrl+E" @click="openEditModal">
                    <Pencil class="h-4 w-4" />
                    Edit
                </BtnSecondary>
            </PageHeader>

            <div class="rounded-xl border border-divider bg-surface p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex min-w-[240px] flex-1 items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-canvas">
                            <Package class="h-6 w-6 text-foreground-soft" />
                        </div>
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-foreground">{{ product.name }}</h2>
                            <div class="mt-1.5 flex items-center gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                        product.is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger',
                                    ]"
                                >
                                    <span :class="['h-1.5 w-1.5 rounded-full', product.is_active ? 'bg-success' : 'bg-danger']"></span>
                                    {{ product.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                        product.stock === 0
                                            ? 'bg-danger/10 text-danger'
                                            : product.stock <= product.stock_warning_level
                                              ? 'bg-warning/10 text-warning'
                                              : 'bg-success/10 text-success',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-1.5 w-1.5 rounded-full',
                                            product.stock === 0
                                                ? 'bg-danger'
                                                : product.stock <= product.stock_warning_level
                                                  ? 'bg-warning'
                                                  : 'bg-success',
                                        ]"
                                    ></span>
                                    {{
                                        product.stock === 0 ? 'Out of Stock' : product.stock <= product.stock_warning_level ? 'Low Stock' : 'In Stock'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="hidden h-14 w-px bg-divider sm:block"></div>

                    <div class="min-w-[120px]">
                        <p class="text-sm font-medium text-foreground-soft">Current Stock</p>
                        <p
                            :class="[
                                'mt-1 text-xl font-bold tabular-nums',
                                product.stock === 0
                                    ? 'text-danger'
                                    : product.stock <= product.stock_warning_level
                                      ? 'text-warning'
                                      : 'text-foreground',
                            ]"
                        >
                            {{ product.stock }}
                        </p>
                    </div>

                    <div class="hidden h-14 w-px bg-divider sm:block"></div>

                    <div class="min-w-[140px]">
                        <p class="text-sm font-medium text-foreground-soft">Price</p>
                        <p class="mt-1 text-xl font-bold text-foreground tabular-nums">
                            {{ formatCurrency(product.price) }}
                        </p>
                    </div>
                </div>

                <div v-if="product.description" class="mt-6 border-t border-divider pt-4">
                    <p class="text-sm leading-relaxed text-foreground-soft">{{ product.description }}</p>
                </div>
            </div>

            <div
                v-if="product.stock <= product.stock_warning_level"
                class="rounded-lg border p-4"
                :class="product.stock === 0 ? 'border-danger bg-danger/10' : 'border-warning bg-warning/10'"
            >
                <div class="flex items-start gap-3">
                    <AlertCircle class="h-5 w-5" :class="product.stock === 0 ? 'text-danger' : 'text-warning'" />
                    <div>
                        <p class="font-medium" :class="product.stock === 0 ? 'text-danger' : 'text-warning'">
                            {{ product.stock === 0 ? 'Out of Stock' : 'Low Stock' }}
                        </p>
                        <p class="text-sm text-foreground-soft">
                            {{
                                product.stock === 0
                                    ? 'This product is currently out of stock.'
                                    : 'This product is running low on stock. Consider restocking soon.'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-divider bg-surface">
                <div class="flex flex-wrap items-stretch divide-x divide-divider">
                    <div class="flex flex-1 flex-col justify-center px-6 py-4">
                        <span class="text-xs text-foreground-soft">Product ID</span>
                        <span class="text-lg font-semibold text-foreground">{{ product.id }}</span>
                    </div>
                    <div class="flex flex-1 flex-col justify-center px-6 py-4">
                        <span class="text-xs text-foreground-soft">Barcode</span>
                        <span class="text-lg font-semibold text-foreground">{{ product.barcode ?? '—' }}</span>
                    </div>
                    <div class="flex flex-1 flex-col justify-center px-6 py-4">
                        <span class="text-xs text-foreground-soft">Warning Level</span>
                        <span class="text-lg font-semibold text-foreground">{{ product.stock_warning_level }}</span>
                    </div>
                    <div v-if="product.creator" class="flex flex-1 flex-col justify-center px-6 py-4">
                        <span class="text-xs text-foreground-soft">Created</span>
                        <span class="text-sm font-medium text-foreground">{{ formatDateTime(product.created_at) }}</span>
                        <span class="text-xs text-foreground-soft">by <UserNameLink :user="product.creator" :return-to="currentUrl" /></span>
                    </div>
                    <div v-if="product.updater" class="flex flex-1 flex-col justify-center px-6 py-4">
                        <span class="text-xs text-foreground-soft">Updated</span>
                        <span class="text-sm font-medium text-foreground">{{ formatDateTime(product.updated_at) }}</span>
                        <span class="text-xs text-foreground-soft">by <UserNameLink :user="product.updater" :return-to="currentUrl" /></span>
                    </div>
                </div>
            </div>

            <StockMovements ref="stockMovementsRef" :movements="movements" />

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
                                    Deletion is only allowed for products with no related records (stock movements, sales, or purchases). Products
                                    with history cannot be deleted.
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
                                    <strong class="text-foreground">"{{ product.name }}"</strong>. If any related records exist, deletion will be
                                    blocked.
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
                                <BtnDanger type="submit" :disabled="deleteConfirmation !== product.name">
                                    I understand, delete this product
                                </BtnDanger>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <ProductFormModal :show="showModal" :product="product" @close="closeModal" />

            <StockAdjustmentModal
                :show="showAdjustModal"
                :product-id="product.id"
                :product-name="product.name"
                :current-stock="product.stock"
                @close="closeAdjustModal"
            />

            <Toast v-if="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />
        </div>
    </AuthenticatedLayout>
</template>
