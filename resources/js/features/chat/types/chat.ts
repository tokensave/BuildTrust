import type { Ad } from '@/features/ads/types/ad';

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
