<script setup lang="ts">
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormInput from '@/components/forms/FormInput.vue';
import Modal from '@/components/ui/Modal.vue';
import { useFormatter } from '@/composables/useFormatter';
import { index as productsIndex } from '@/routes/api/products';
import type { Product } from '@/types/inventory';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { nextTick, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    select: [product: Product];
}>();

const { formatCurrency } = useFormatter();

const search = ref('');
const products = ref<Product[]>([]);
const isLoading = ref(false);
const focusedIndex = ref<number>(-1);
const rowRefs = ref<HTMLTableRowElement[]>([]);
const searchInputRef = ref<HTMLInputElement | null>(null);

const resetFocusState = () => {
    focusedIndex.value = -1;
    rowRefs.value = [];
};

const focusRow = (index: number) => {
    nextTick(() => {
        rowRefs.value[index]?.focus();
    });
};

const loadProducts = async (searchTerm = '') => {
    isLoading.value = true;
    try {
        const response = await axios.get(productsIndex.url(), {
            params: searchTerm ? { search: searchTerm } : {},
        });
        products.value = response.data;
    } catch (error) {
        console.error('Failed to load products:', error);
    } finally {
        isLoading.value = false;
    }
};

const selectProduct = (product: Product) => {
    if (product.stock <= 0) {
        return;
    }
    emit('select', product);
    emit('close');
};

const close = () => emit('close');

const findNextAvailableIndex = (currentIndex: number, direction: 'up' | 'down'): number => {
    if (direction === 'down') {
        if (currentIndex + 1 < products.value.length) {
            return currentIndex + 1;
        }
        return currentIndex;
    } else {
        if (currentIndex - 1 >= 0) {
            return currentIndex - 1;
        }
        return currentIndex;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        searchInputRef.value?.focus();
        return;
    }

    if (!products.value.length) return;

    const active = document.activeElement;
    const isInput = active instanceof HTMLInputElement || active instanceof HTMLTextAreaElement || active instanceof HTMLSelectElement;

    if (isInput && e.key !== 'ArrowDown') {
        return;
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (isInput) {
                focusedIndex.value = 0;
            } else {
                focusedIndex.value = findNextAvailableIndex(focusedIndex.value, 'down');
            }
            focusRow(focusedIndex.value);
            break;
        case 'ArrowUp':
            e.preventDefault();
            focusedIndex.value = findNextAvailableIndex(focusedIndex.value, 'up');
            focusRow(focusedIndex.value);
            break;
        case 'Enter':
            if (focusedIndex.value >= 0 && products.value[focusedIndex.value]) {
                e.preventDefault();
                selectProduct(products.value[focusedIndex.value]);
            }
            break;
        case 'Escape':
            close();
            break;
    }
};

watch(
    () => props.show,
    (show) => {
        if (show) {
            search.value = '';
            resetFocusState();
            loadProducts();
        } else {
            resetFocusState();
        }
    },
);

watch(
    products,
    () => {
        if (products.value.length === 0) {
            focusedIndex.value = -1;
            nextTick(() => {
                searchInputRef.value?.focus();
            });
        } else if (focusedIndex.value >= 0 && focusedIndex.value < products.value.length) {
            focusRow(focusedIndex.value);
        }
    },
    { deep: true },
);

const debouncedSearch = useDebounceFn((value: string) => {
    loadProducts(value);
}, 500);

watch(search, (value) => {
    debouncedSearch(value);
});
</script>

<template>
    <Modal :show="show" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">Product Lookup</h3>
                <p class="text-sm text-foreground-soft">Search for a product to add to cart</p>
            </div>
        </template>

        <div class="space-y-4">
            <FormInput ref="searchInputRef" v-model="search" placeholder="Search products... (Ctrl+F)" variant="canvas" @keydown="handleKeydown" />

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-foreground-soft">
                <span><kbd>↑/↓</kbd> Navigate Rows</span>
                <span><kbd>Enter</kbd> Select Product</span>
            </div>

            <div v-if="isLoading" class="text-center text-foreground-soft">Loading...</div>

            <div v-else-if="products.length === 0" class="text-center text-foreground-soft">No products found</div>

            <div v-else class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-divider text-left text-foreground-soft">
                        <tr>
                            <th class="px-3 pb-2">Product</th>
                            <th class="px-3 pb-2 text-right">Price</th>
                            <th class="px-3 pb-2 text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(product, index) in products"
                            :key="product.id"
                            :ref="
                                (el) => {
                                    if (el) rowRefs[index] = el as HTMLTableRowElement;
                                }
                            "
                            tabindex="0"
                            class="cursor-pointer border-b border-divider transition-colors outline-none"
                            :class="{
                                'bg-primary-50/60 ring-1 ring-primary-600/30 ring-inset dark:bg-primary-900/20': focusedIndex === index,
                            }"
                            @click="selectProduct(product)"
                            @focus="focusedIndex = index"
                            @mouseenter="focusedIndex = index"
                            @keydown="handleKeydown"
                        >
                            <td class="px-3 py-2 font-medium text-foreground">
                                {{ product.name }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                {{ formatCurrency(product.price || 0) }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span v-if="product.stock > 0">{{ product.stock }}</span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full bg-danger px-2 py-0.5 text-xs font-medium whitespace-nowrap text-white"
                                    >Out of Stock</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <template #footer>
            <BtnSecondary type="button" @click="close">Cancel</BtnSecondary>
        </template>
    </Modal>
</template>
