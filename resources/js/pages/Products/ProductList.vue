<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import FormInput from '@/components/forms/FormInput.vue';
import DetailLink from '@/components/links/DetailLink.vue';
import DataTable from '@/components/tables/DataTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Toast from '@/components/ui/Toast.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { show } from '@/routes/products';
import type { Product } from '@/types/inventory';
import type { PaginationData } from '@/types/pagination';
import { usePage } from '@inertiajs/vue3';
import { Plus, Search, Upload } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import ProductCsvImportModal from './Partials/ProductCsvImportModal.vue';
import ProductFormModal from './Partials/ProductFormModal.vue';

const props = defineProps<{
    products: PaginationData;
}>();

const page = usePage();
const { buildDetailUrl, get, set, setRouterOptions } = useQueryStrings();
const { formatCurrency } = useFormatter();

setRouterOptions({ only: ['products'] });

const search = computed({
    get: () => get('search') ?? '',
    set: (val) => set('search', val || undefined),
});

const showToast = ref(false);
const toastMessage = ref('');

const hideToast = () => {
    showToast.value = false;
};

const searchInputRef = ref<InstanceType<typeof FormInput> | null>(null);
const showModal = ref(false);
const selectedProduct = ref<Product | null>(null);
const showImportModal = ref(false);

onMounted(() => {
    nextTick(() => searchInputRef.value?.focus());
});

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            toastMessage.value = message;
            showToast.value = true;
        }
    },
    { immediate: true },
);

useKeybinds([
    { key: 'f', ctrl: true, handler: () => searchInputRef.value?.focus() },
    { key: 'n', ctrl: true, handler: () => openModal() },
]);

const openModal = (product: Product | null = null) => {
    (document.activeElement as HTMLElement)?.blur();
    selectedProduct.value = product;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedProduct.value = null;
};

const openImportModal = () => {
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <PageHeader title="Products">
                <div class="relative w-64">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-foreground-soft" />
                    <FormInput
                        ref="searchInputRef"
                        v-model="search"
                        type="text"
                        placeholder="Search products..."
                        variant="surface"
                        keybind="Ctrl+F"
                        class="pl-10"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <BtnPrimary keybind="Ctrl+N" @click="openModal()">
                        <Plus class="h-4 w-4" />
                        Add Product
                    </BtnPrimary>
                    <button
                        class="rounded-md border border-divider bg-surface px-3 py-2 text-sm font-medium text-foreground hover:bg-hover"
                        @click="openImportModal"
                    >
                        <Upload class="mr-1 inline h-4 w-4" />
                        Import from CSV
                    </button>
                </div>
            </PageHeader>

            <div class="flex items-center gap-4 text-xs text-foreground-soft">
                <span
                    ><kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">↑</kbd
                    ><kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">↓</kbd>: Navigate rows</span
                >
                <span
                    ><kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">Ctrl</kbd>+<kbd
                        class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground"
                        >←</kbd
                    >/<kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">→</kbd>: Navigate pages</span
                >
            </div>

            <div
                class="rounded-lg bg-surface p-6 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
            >
                <DataTable
                    :columns="[
                        { key: 'name', label: 'Name', sortable: true, width: 'fill' },
                        { key: 'barcode', label: 'Barcode', sortable: true },
                        { key: 'stock', label: 'Stock', align: 'right', sortable: true },
                        { key: 'price', label: 'Price', align: 'right', sortable: true },
                        { key: 'is_active', label: 'Status' },
                    ]"
                    :data="products"
                >
                    <template #cell-name="{ row }">
                        <DetailLink :href="buildDetailUrl(show.url(row.id))" tabindex="-1">
                            {{ row.name }}
                        </DetailLink>
                    </template>
                    <template #cell-barcode="{ value }">
                        <span class="text-foreground">{{ value }}</span>
                    </template>
                    <template #cell-stock="{ value }">
                        <span
                            :class="[
                                'text-foreground',
                                value === 0 ? 'text-red-600 dark:text-red-400' : value <= 10 ? 'text-amber-600 dark:text-amber-400' : '',
                            ]"
                        >
                            {{ value }}
                        </span>
                    </template>
                    <template #cell-price="{ value }">
                        <span class="text-foreground">{{ formatCurrency(value) }}</span>
                    </template>
                    <template #cell-is_active="{ value }">
                        <span
                            :class="[
                                'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                value
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            ]"
                        >
                            {{ value ? 'Active' : 'Inactive' }}
                        </span>
                    </template>
                    <template #pagination="{ links, from, to, total }">
                        <p class="text-sm text-foreground-soft">
                            Showing <span class="font-medium text-foreground">{{ from }}-{{ to }}</span> of
                            <span class="font-medium text-foreground">{{ total }}</span> results
                        </p>
                        <div class="flex items-center gap-0.5">
                            <component
                                :is="link.url ? 'Link' : 'span'"
                                v-for="link in links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'inline-flex h-8 min-w-[2rem] items-center justify-center rounded-md px-2 text-sm font-medium transition-colors',
                                    link.active ? 'bg-primary-600 text-white' : 'text-foreground-soft hover:bg-hover hover:text-foreground',
                                    !link.url && 'pointer-events-none opacity-40',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </template>
                </DataTable>
            </div>

            <ProductFormModal :show="showModal" :product="selectedProduct ?? undefined" @close="closeModal" />

            <Toast v-if="showToast" :message="toastMessage" type="success" @close="hideToast" />
            <ProductCsvImportModal :show="showImportModal" max-width="lg" @close="closeImportModal" @imported="closeImportModal" />
        </div>
    </AuthenticatedLayout>
</template>
