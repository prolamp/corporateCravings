export type AppRole =
    | 'super_admin'
    | 'admin'
    | 'manager'
    | 'assistant'
    | 'subscriber'
    | 'customer';

export type User = {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    role?: AppRole | string | null;
    role_label?: string | null;
    roles?: string[];
    permissions?: string[];
    is_management?: boolean;
    is_super_admin?: boolean;
    is_admin?: boolean;
    is_manager?: boolean;
    is_assistant?: boolean;
    is_subscriber?: boolean;
    is_customer?: boolean;
    creatable_roles?: Array<{ value: string; label: string }>;
    can_view_earnings?: boolean;
    can_view_profit_loss?: boolean;
    can_view_payments?: boolean;
    dashboard_route?: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
