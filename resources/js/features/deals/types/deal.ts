import type { User, Company } from '@/types';

export type DealStatus = 'pending' | 'accepted' | 'rejected' | 'completed' | 'canceled';

export interface Deal {
    id: number;
    ad_id: number;
    buyer_id: number;
    seller_id: number;
    price: number;
    notes?: string;
    status: DealStatus;
    created_at: string;
    documents_urls?: string[];
    buyer: {
        id: number;
        name: string;
        company?: Company;
    };
    seller: {
        id: number;
        name: string;
        company?: Company;
    };
    ad_title: string;
}

export interface StatusConfig {
    value: DealStatus;
    label: string;
    color: string;
}
