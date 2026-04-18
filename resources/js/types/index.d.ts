export interface PersonName {
    first_name: string;
    middle_name: string | null;
    last_name: string;
    name_extension: string | null;
    full_name?: string;
}

export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash?: {
        error?: string;
        success?: string;
    };
};

export interface User extends PersonName, Timestamps {
    id: number;
    username: string;
    role: 'admin' | 'manager' | 'inventory' | 'auditor' | 'cashier';
    is_active: boolean;
    created_by: number | null;
    updated_by: number | null;
    last_login_at: string | null;
    deletable?: boolean;
    creator?: Pick<User, 'id' | 'first_name' | 'last_name'>;
    updater?: Pick<User, 'id' | 'first_name' | 'last_name'>;
}

export interface Timestamps {
    created_at: string;
    updated_at: string;
}

export interface Creatable {
    created_by: number | null;
    creator?: User;
}

export interface Updatable {
    updated_by: number | null;
    updater?: User;
}

export interface Auditable extends Creatable, Updatable {}
