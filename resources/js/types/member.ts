import type { Auditable, Creatable, PersonName, Timestamps } from './index';

export interface Member extends Auditable, PersonName, Timestamps {
    id: number;
    phone: string | null;
    address: string | null;
    is_active: boolean;
    balance: number;
    share_capital: number;
    tin_number: string | null;
    deletable?: boolean;
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
