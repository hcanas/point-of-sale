import type { Creatable, Timestamps } from './index';

export interface CashMovement extends Creatable, Timestamps {
    id: number;
    amount: number;
    reference_type: 'sale' | 'purchase' | 'payment' | 'refund' | 'adjustment';
    reference_id: number | null;
    notes: string | null;
}
