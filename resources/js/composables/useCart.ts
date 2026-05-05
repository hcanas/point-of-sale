import type { Member } from '@/types/member';
import { computed, ref } from 'vue';

interface CartItem {
    id: number;
    name: string;
    barcode: string;
    price: number;
    quantity: number;
    stock: number;
}

type CartMember = Pick<Member, 'id' | 'formal_name' | 'outstanding_balance'>;

interface CartData {
    items: CartItem[];
    member: CartMember | null;
}

const CART_STORAGE_KEY = 'pos_cart';

const cartData = ref<CartData>({
    items: [],
    member: null,
});

const loadCart = () => {
    const saved = localStorage.getItem(CART_STORAGE_KEY);
    if (saved) {
        try {
            cartData.value = JSON.parse(saved);
        } catch (e) {
            console.error('Failed to parse cart data:', e);
            cartData.value = { items: [], member: null };
        }
    }
};

const saveCart = () => {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartData.value));
};

const cart = computed(() => cartData.value.items);
const member = computed(() => cartData.value.member);

const cartTotal = computed(() => {
    const total = cartData.value.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    return Math.round(total * 100) / 100;
});

const addToCart = (product: { id: number; name: string; barcode: string; price: number; stock: number }) => {
    const existingIndex = cartData.value.items.findIndex((item) => item.id === product.id);
    if (existingIndex >= 0) {
        cartData.value.items[existingIndex].quantity += 1;
    } else {
        cartData.value.items.push({
            id: product.id,
            name: product.name,
            barcode: product.barcode,
            price: product.price,
            quantity: 1,
            stock: product.stock,
        });
    }
    saveCart();
};

const updateQuantity = (itemId: number, quantity: number) => {
    const item = cartData.value.items.find((item) => item.id === itemId);
    if (item && quantity >= 1 && quantity <= item.stock) {
        item.quantity = quantity;
        saveCart();
    }
};

const updatePrice = (itemId: number, price: number) => {
    const item = cartData.value.items.find((item) => item.id === itemId);
    if (item && price >= 0) {
        item.price = price;
        saveCart();
    }
};

const removeFromCart = (itemId: number) => {
    cartData.value.items = cartData.value.items.filter((item) => item.id !== itemId);
    saveCart();
};

const clearCart = () => {
    cartData.value.items = [];
    saveCart();
};

const setMember = (member: CartMember | null) => {
    cartData.value.member = member;
    saveCart();
};

const clearMember = () => {
    cartData.value.member = null;
    saveCart();
};

const resetCart = () => {
    cartData.value = { items: [], member: null };
    saveCart();
};

export const useCart = () => {
    loadCart();

    return {
        cart,
        member,
        cartTotal,
        addToCart,
        updateQuantity,
        updatePrice,
        removeFromCart,
        clearCart,
        setMember,
        clearMember,
        resetCart,
    };
};
