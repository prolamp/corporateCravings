import type { Auth } from '@/types/auth';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            site: {
                brand: {
                    name: string;
                    phone: string;
                    whatsapp: string;
                    email: string;
                    address: string;
                    hours: string;
                };
                whatsapp_url: string | null;
                analytics: {
                    ga_measurement_id: string | null;
                };
            };
            seo: {
                site_name: string;
                title_suffix: string;
                default_description: string;
                default_image: string | null;
                locale: string;
                app_url: string;
                organization: Record<string, unknown>;
            };
            cart: {
                count: number;
                quantity: number;
            };
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
