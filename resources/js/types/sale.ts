import type { Creatable, Timestamps } from './index';
import type { Product } from './inventory';
import type { Member } from './member';

export interface Sale extends Creatable, Timestamps {
    id: number;
    reference_number: string;
    member_id: number | null;
    member_name: string;
    member_short_name: string;
    total_amount: number;
    amount_paid: number;
    amount_tendered: number;
    change_given: number;
    notes: string | null;
    status?: 'completed' | 'partially_paid' | 'unpaid';
}

export interface SaleItem extends Creatable, Timestamps {
    id: number;
    sale_id: number;
    product_id: number | null;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

export interface SaleReturn extends Creatable, Timestamps {
    id: number;
    sale_id: number;
    member_id: number | null;
    total_refund_amount: number;
    notes: string | null;
}

export interface SaleReturnItem extends Creatable, Timestamps {
    id: number;
    sale_return_id: number;
    sale_item_id: number | null;
    product_id: number | null;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

export interface Payment extends Creatable, Timestamps {
    id: number;
    sale_id: number | null;
    member_id: number | null;
    type: 'cash' | 'credit';
    amount: number;
    amount_tendered: number;
    change_given: number;
    notes: string | null;
}

export interface SaleItemWithProduct extends SaleItem {
    product: Product | null;
}

export interface SaleReturnItemWithProduct extends SaleReturnItem {
    product: Product | null;
}

export interface SaleReturnWithItems extends SaleReturn {
    items: SaleReturnItemWithProduct[];
}

export interface SaleFull extends Sale {
    member: Member | null;
    items: SaleItemWithProduct[];
    payments: Payment[];
    returns: SaleReturnWithItems[];
}
