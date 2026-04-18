import type { Creatable, Timestamps } from './index';

export interface Purchase extends Creatable, Timestamps {
    id: number;
    reference_number: string | null;
    vendor_name: string | null;
    total_amount: number;
    notes: string | null;
}

export interface PurchaseItem extends Creatable, Timestamps {
    id: number;
    purchase_id: number;
    product_id: number | null;
    quantity: number;
    unit_cost: number;
    subtotal: number;
}
