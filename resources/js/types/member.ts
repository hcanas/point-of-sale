import type { Auditable, Creatable, Timestamps } from './index';

export interface Member extends Auditable, Timestamps {
    id: number;
    phone: string;
    address: string;
    is_active: boolean;
    credit_limit: number;
    balance: number;
    share_capital: number;
    tin_number: string;
}

export interface MemberLedger extends Creatable, Timestamps {
    id: number;
    member_id: number;
    amount: number;
    balance_after: number;
    reference_type: 'purchase' | 'payment' | 'adjustment' | 'initial';
    reference_id: number | null;
    notes: string | null;
}
