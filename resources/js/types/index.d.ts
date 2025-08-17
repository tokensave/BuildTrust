import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import type { AD_TYPES, ALL_CATEGORIES, SUBCATEGORIES_BY_CATEGORY, AD_STATUS_OPTIONS } from './ad-enums';



export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    threads?: Thread[];
}

export interface User {
    id: number;
    username: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    avatar_url: string;
    company?: Company;
}

// Добавьте типы
export type AdTypeOption = typeof AD_TYPES[number];
export type AdCategoryOption = typeof ALL_CATEGORIES[number];
export type AdStatusOption = typeof AD_STATUS_OPTIONS[number];
export type AdSubCategoryOption = typeof SUBCATEGORIES_BY_CATEGORY[number];
export interface Ad {
    id: number;
    title: string;
    type: string;
    category?: string;
    subcategory?: string;
    location?: string;
    description: string;
    price: number;
    is_urgent: boolean;
    features?: string[];
    slug: string;
    status: string;
    user_id: number;
    formatted_category?: string; // Аксессор для красивого отображения категорий
    is_service: boolean; // Аксессор для проверки типа
    created_at: string;
    updated_at: string;
    media?: {
        original_url: string;
    }[];
    user: {
        id: number;
        name: string;
        company?: Company;
    };
}

// Типы для фильтров
export interface AdFilter {
    type?: string;
    category?: string;
    subcategory?: string;
    location?: string;
    min_price?: number;
    max_price?: number;
    urgent?: boolean;
    search?: string;
}

export interface AdType {
    value: string;
    label: string;
    description: string;
}

export interface AdCategory {
    value: string;
    label: string;
}

export interface AdSubcategory {
    value: string;
    label: string;
}

export interface CategoriesStructure {
    [categoryKey: string]: {
        label: string;
        subcategories: {
            [subcategoryKey: string]: AdSubcategory;
        };
    };
}

export interface Company {
    inn: string;
    name: string;
    email: string;
    phone: string;
    city: string;
    address: string;
    website: string;
    verified: boolean;
}


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

export type DealStatus = 'pending' | 'accepted' | 'rejected' | 'completed' | 'canceled';

export interface Thread {
    id: number;
    ad: Ad;
    messages: Message[];
    participants: Array<{
        id: number;
        username: string;
        email: string;
    }>;
    latest_message: {
        content: string;
        created_at: string;
        author_id: number;
    } | null;
}

export interface Message {
    id: number;
    content: string;
    created_at: string;
    author_id: number;
    thread_id: number;
    author: {
        id: number;
        username: string;
        email: string;
    };
}

export type BreadcrumbItemType = BreadcrumbItem;
