import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

// Реэкспорт типов из features для обратной совместимости
export type { Ad, AdStatus, AdType, AdCategory, AdFilters } from '@/features/ads/types/ad';
export type { Deal, DealStatus, StatusConfig } from '@/features/deals/types/deal';
export type { Thread, Message } from '@/features/chat/types/chat';

// Глобальные интерфейсы приложения
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

// Утилитарные типы
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

// Алиас для обратной совместимости
export type BreadcrumbItemType = BreadcrumbItem;
