import type { Auditable, Creatable, Timestamps, User } from './index';

export interface Product extends Auditable, Timestamps {
    id: number;
    barcode: string | null;
    name: string;
    description: string | null;
    stock: number;
    stock_warning_level: number;
    price: number;
    is_active: boolean;
    deletable?: boolean;
}

export interface StockMovement extends Creatable, Timestamps {
    id: number;
    product_id: number;
    quantity: number;
    after_quantity: number;
    reference_type: 'sale' | 'restock' | 'return' | 'adjustment' | 'initial';
    reference_id: number | null;
    notes: string | null;
}

export interface ProductWithRelations extends Product {
    creator?: User;
    updater?: User;
}
