<script setup lang="ts">
import BtnDangerOutline from '@/components/buttons/BtnDangerOutline.vue';
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import FormCurrency from '@/components/forms/FormCurrency.vue';
import FormNumberInput from '@/components/forms/FormNumberInput.vue';
import { useCart } from '@/composables/useCart';
import { useFormatter } from '@/composables/useFormatter';
import { useKeybinds } from '@/composables/useKeybinds';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import pos from '@/routes/pos';
import axios from 'axios';
import { Package, Receipt, Trash, User } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';
import MemberLookupModal from './Partials/MemberLookupModal.vue';
import ProductLookupModal from './Partials/ProductLookupModal.vue';
import SaleSummaryModal from './Partials/SaleSummaryModal.vue';
import TransactionsLookupModal from './Partials/TransactionsLookupModal.vue';

const { cart, member, cartTotal, addToCart, updateQuantity, updatePrice, removeFromCart, clearCart, setMember } = useCart();
const { formatCurrency } = useFormatter();

const showMemberModal = ref(false);
const showProductModal = ref(false);
const showSaleSummaryModal = ref(false);
const showTransactionsLookupModal = ref(false);
const completedSale = ref<any>(null);
const cartErrors = ref<Record<number, string>>({});
const amountTendered = ref<number>(0);
const amountTenderedRef = ref<HTMLInputElement | null>(null);
const cartFocusedIndex = ref<number>(-1);
const cartRowRefs = ref<HTMLTableRowElement[]>([]);
const priceInputRefs = ref<any[]>([]);
const isCompletingSale = ref(false);

const selectMember = (memberData: any) => {
    setMember({
        id: memberData.id,
        formal_name: memberData.formal_name,
        outstanding_balance: memberData.outstanding_balance || 0,
    });
};

const removeMember = () => {
    setMember(null);
};

const handleCartKeydown = (e: KeyboardEvent) => {
    const index = cartFocusedIndex.value;
    if (index < 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (index < cart.value.length - 1) {
            cartFocusedIndex.value = index + 1;
            nextTick(() => {
                cartRowRefs.value[cartFocusedIndex.value]?.focus();
            });
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (index > 0) {
            cartFocusedIndex.value = index - 1;
            nextTick(() => {
                cartRowRefs.value[cartFocusedIndex.value]?.focus();
            });
        }
    } else if (e.key === '+' || e.key === '=') {
        e.preventDefault();
        updateQuantity(cart.value[index].id, cart.value[index].quantity + 1);
    } else if (e.key === '-' || e.key === '_') {
        e.preventDefault();
        if (cart.value[index].quantity > 1) {
            updateQuantity(cart.value[index].id, cart.value[index].quantity - 1);
        }
    } else if (e.key === 'Delete') {
        e.preventDefault();
        removeFromCart(cart.value[index].id);
        if (cartFocusedIndex.value >= cart.value.length) {
            cartFocusedIndex.value = cart.value.length - 1;
        }
    }
};

watch(cart, () => {
    cartFocusedIndex.value = -1;
    cartRowRefs.value = [];
    priceInputRefs.value = [];
    cartErrors.value = {};
});

const selectProduct = (product: any) => {
    addToCart(product);
};

const completeSale = async () => {
    if (!member.value || cart.value.length === 0 || isCompletingSale.value) {
        return;
    }

    isCompletingSale.value = true;

    try {
        const response = await axios.post(pos.store.post().url, {
            member_id: member.value.id,
            amount_tendered: amountTendered.value,
            items: cart.value.map((item) => ({
                id: item.id,
                quantity: item.quantity,
                price: item.price,
            })),
        });

        if (response.data.success) {
            completedSale.value = response.data.sale;
            showSaleSummaryModal.value = true;

            clearCart();
            setMember(null);
            amountTendered.value = 0;
        }
    } catch (error: any) {
        if (error.response?.data?.errors) {
            cartErrors.value = error.response.data.errors;
        } else {
            console.error('Error completing sale:', error);
        }
    } finally {
        isCompletingSale.value = false;
    }
};

useKeybinds([
    { key: 'F2', handler: () => (showProductModal.value = true) },
    { key: 'F3', handler: () => (showMemberModal.value = true) },
    { key: 'F4', handler: () => (showTransactionsLookupModal.value = true) },
    { key: 'F8', handler: () => amountTenderedRef.value?.focus() },
    { key: 'Delete', ctrl: true, handler: () => clearCart() },
    {
        key: 'F12',
        handler: () => {
            completeSale();
        },
    },
    {
        key: 'ArrowDown',
        handler: () => {
            if (cart.value.length === 0) return;
            const targetIndex = cartFocusedIndex.value < 0 ? 0 : Math.min(cartFocusedIndex.value + 1, cart.value.length - 1);
            cartFocusedIndex.value = targetIndex;
            nextTick(() => {
                cartRowRefs.value[targetIndex]?.focus();
            });
        },
    },
    {
        key: 'ArrowUp',
        handler: () => {
            if (cart.value.length === 0) return;
            const targetIndex = cartFocusedIndex.value < 0 ? cart.value.length - 1 : Math.max(cartFocusedIndex.value - 1, 0);
            cartFocusedIndex.value = targetIndex;
            nextTick(() => {
                cartRowRefs.value[targetIndex]?.focus();
            });
        },
    },
    {
        key: 'e',
        handler: () => {
            const index = cartFocusedIndex.value;
            if (index >= 0 && index < cart.value.length) {
                nextTick(() => {
                    priceInputRefs.value[index]?.focus();
                });
            }
        },
    },
]);
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-4">
            <div class="flex gap-4">
                <div
                    class="flex-1 flex-shrink-0 rounded-lg bg-surface p-4 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
                >
                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-foreground">Cart</h2>
                            <BtnDangerOutline keybind="Ctrl+Del" @click="clearCart">Clear Cart</BtnDangerOutline>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-foreground-soft">
                            <span><kbd>↑/↓</kbd> Navigate Items</span>
                            <span><kbd>+/-</kbd> Adjust Quantity</span>
                            <span><kbd>E</kbd> Edit Price</span>
                            <span><kbd>Delete</kbd> Remove Item</span>
                        </div>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-divider text-left text-foreground-soft">
                                <th class="w-1/2 px-3 py-2">Product</th>
                                <th class="w-32 px-3 py-2 text-center">Quantity</th>
                                <th class="w-28 px-3 py-2 text-right">Price</th>
                                <th class="w-28 px-3 py-2 text-right">Total</th>
                                <th class="w-10 px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in cart"
                                :key="item.id"
                                :ref="
                                    (el) => {
                                        if (el) cartRowRefs[index] = el as HTMLTableRowElement;
                                    }
                                "
                                class="group border-b border-divider transition-colors outline-none hover:bg-canvas"
                                tabindex="-1"
                                :class="{
                                    'bg-canvas': cartFocusedIndex === index,
                                    'bg-danger/10': cartErrors[index],
                                }"
                                @focus="cartFocusedIndex = index"
                                @mouseenter="cartFocusedIndex = index"
                                @keydown="handleCartKeydown"
                            >
                                <td class="px-3 py-2 text-foreground">
                                    <div>{{ item.name }}</div>
                                    <div v-if="cartErrors[index]" class="text-xs text-danger">{{ cartErrors[index] }}</div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <FormNumberInput
                                        :model-value="item.quantity"
                                        :min="1"
                                        :max="item.stock"
                                        class="w-10 text-right [&::-webkit-inner-spin-button]:hidden [&::-webkit-outer-spin-button]:hidden"
                                        variant="canvas"
                                        tabindex="-1"
                                        @update:model-value="updateQuantity(item.id, Number($event) || 1)"
                                        @blur="
                                            (e: FocusEvent) => {
                                                const value = Number((e.target as HTMLInputElement).value);
                                                if (value > item.stock) updateQuantity(item.id, item.stock);
                                            }
                                        "
                                    />
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <FormCurrency
                                        :model-value="item.price"
                                        :min="0"
                                        class="text-right"
                                        variant="canvas"
                                        tabindex="-1"
                                        :ref="
                                            (el) => {
                                                if (el) priceInputRefs[index] = el as any;
                                            }
                                        "
                                        @update:model-value="updatePrice(item.id, Number($event) || 0)"
                                    />
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-foreground">
                                    {{ formatCurrency(item.price * item.quantity) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button
                                        class="rounded p-1 text-danger opacity-0 transition-opacity duration-150 group-hover:opacity-100 hover:bg-danger/10"
                                        :class="{ 'opacity-100': cartFocusedIndex === index }"
                                        tabindex="-1"
                                        @click="removeFromCart(item.id)"
                                    >
                                        <Trash :size="16" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="cart.length === 0">
                                <td colspan="5" class="py-8 text-center text-foreground-soft">
                                    <div class="text-sm">Cart is empty</div>
                                    <div class="mt-1 text-xs opacity-60">Press <span class="font-mono">F2</span> to add products</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="w-72 space-y-4">
                    <div
                        class="rounded-lg bg-surface p-4 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
                    >
                        <h2 class="mb-4 text-lg font-semibold text-foreground">Order Summary</h2>
                        <div class="space-y-4">
                            <div class="rounded-md border border-divider bg-canvas p-3">
                                <div v-if="member" class="relative">
                                    <button
                                        class="absolute top-0 right-0 rounded text-danger hover:bg-danger/10 focus-visible:ring-2 focus-visible:ring-danger"
                                        @click="removeMember"
                                    >
                                        ×
                                    </button>
                                    <div class="mb-1 text-xs text-foreground-soft">Selected Member</div>
                                    <div class="pr-6 text-xs font-medium text-foreground">{{ member.formal_name }}</div>
                                    <div class="mt-1 text-xs">
                                        <span class="text-foreground-soft">Outstanding Balance: </span>
                                        <span class="font-medium text-foreground">{{
                                            member.outstanding_balance ? formatCurrency(member.outstanding_balance) : '$0.00'
                                        }}</span>
                                        <span v-if="Math.max(0, cartTotal - Number(amountTendered)) > 0" class="text-danger">
                                            (+{{ formatCurrency(Math.max(0, cartTotal - Number(amountTendered))) }})</span
                                        >
                                    </div>
                                </div>
                                <button
                                    v-else
                                    class="flex w-full items-center justify-center text-sm text-foreground-soft outline-none hover:text-foreground focus-visible:ring-2 focus-visible:ring-primary-600"
                                    @click="showMemberModal = true"
                                >
                                    <span>Select Member</span>
                                    <kbd class="ml-2">F3</kbd>
                                </button>
                            </div>

                            <div class="border-t border-divider"></div>

                            <div class="flex flex-col items-center">
                                <span class="text-sm text-foreground-soft">Total</span>
                                <span class="text-3xl font-bold text-foreground">{{ formatCurrency(cartTotal) }}</span>
                            </div>

                            <div class="border-t border-divider"></div>

                            <div class="space-y-2">
                                <label class="block text-sm text-foreground-soft">Amount Tendered <kbd>F8</kbd></label>
                                <FormNumberInput
                                    ref="amountTenderedRef"
                                    v-model="amountTendered"
                                    :min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    variant="canvas"
                                    :disabled="!member"
                                />
                                <div class="flex justify-between text-sm">
                                    <span class="text-foreground-soft">{{ Number(amountTendered) < cartTotal ? 'Balance Due:' : 'Change:' }}</span>
                                    <span class="font-medium" :class="Number(amountTendered) < cartTotal ? 'text-danger' : 'text-success'">{{
                                        formatCurrency(Math.abs(Number(amountTendered) - cartTotal))
                                    }}</span>
                                </div>
                            </div>

                            <BtnPrimary
                                class="w-full justify-center text-center"
                                keybind="F12"
                                :disabled="!member || cart.length === 0 || isCompletingSale"
                                @click="completeSale"
                            >
                                {{ isCompletingSale ? 'Completing...' : 'Complete Sale' }}
                            </BtnPrimary>
                        </div>
                    </div>

                    <div
                        class="rounded-lg bg-surface p-4 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
                    >
                        <h2 class="mb-4 text-lg font-semibold text-foreground">Quick Actions</h2>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                class="flex flex-col items-center justify-center rounded-md border border-divider bg-canvas p-3 outline-none hover:bg-hover focus-visible:ring-2 focus-visible:ring-primary-600"
                                @click="showProductModal = true"
                            >
                                <Package class="h-5 w-5 text-foreground" />
                                <span class="mt-1 text-xs font-medium text-foreground">Product</span>
                                <kbd class="mt-1">F2</kbd>
                            </button>
                            <button
                                class="flex flex-col items-center justify-center rounded-md border border-divider bg-canvas p-3 outline-none hover:bg-hover focus-visible:ring-2 focus-visible:ring-primary-600"
                                @click="showMemberModal = true"
                            >
                                <User class="h-5 w-5 text-foreground" />
                                <span class="mt-1 text-xs font-medium text-foreground">Member</span>
                                <kbd class="mt-1">F3</kbd>
                            </button>
                            <button
                                class="flex flex-col items-center justify-center rounded-md border border-divider bg-canvas p-3 outline-none hover:bg-hover focus-visible:ring-2 focus-visible:ring-primary-600"
                                @click="showTransactionsLookupModal = true"
                            >
                                <Receipt class="h-5 w-5 text-foreground" />
                                <span class="mt-1 text-xs font-medium text-foreground">Transactions</span>
                                <kbd class="mt-1">F4</kbd>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <MemberLookupModal :show="showMemberModal" @close="showMemberModal = false" @select="selectMember" />
        <ProductLookupModal :show="showProductModal" @close="showProductModal = false" @select="selectProduct" />
        <SaleSummaryModal :show="showSaleSummaryModal" :sale="completedSale" @close="showSaleSummaryModal = false" />
        <TransactionsLookupModal :show="showTransactionsLookupModal" @close="showTransactionsLookupModal = false" />
    </AuthenticatedLayout>
</template>
