<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    ad: {
        id: number;
        title: string;
        type?: string;
        category?: string;
        subcategory?: string;
        location?: string;
        description?: string;
        price?: number;
        is_urgent?: boolean;
        features?: string[];
        formatted_category?: string;
        is_service?: boolean;
        user_id?: number;
        media?: any[];
    };
}>();

const image = props.ad.media?.[0]?.original_url ?? 'https://via.placeholder.com/400x200?text=Нет+изображения';
</script>

<template>
    <div class="rounded-xl shadow hover:shadow-md transition overflow-hidden bg-white dark:bg-gray-900 relative">
        <!-- Бейджи на изображении -->
        <div class="relative">
            <img :src="image" class="w-full h-48 object-cover" alt="Ad Image" />
            <div v-if="ad.is_urgent || ad.is_service" class="absolute top-2 right-2 flex gap-1">
                <Badge v-if="ad.is_urgent" variant="destructive" class="text-xs">
                    Срочно
                </Badge>
                <Badge v-if="ad.is_service" variant="secondary" class="text-xs">
                    Услуга
                </Badge>
            </div>
        </div>
        
        <div class="p-4">
            <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold text-lg truncate flex-1 mr-2">{{ ad.title }}</h3>
                <div v-if="ad.price" class="text-sm font-semibold text-green-600 whitespace-nowrap">
                    {{ ad.price }} ₽
                </div>
                <div v-else-if="!ad.is_service" class="text-sm text-gray-500 whitespace-nowrap">
                    Договорная
                </div>
            </div>
            
            <!-- Категория и местоположение -->
            <div class="text-xs text-gray-500 mb-2 space-y-1">
                <div v-if="ad.formatted_category">
                    {{ ad.formatted_category }}
                </div>
                <div v-if="ad.location" class="flex items-center">
                    📍 {{ ad.location }}
                </div>
            </div>
            
            <p class="text-sm text-gray-500 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ ad.description }}
            </p>
            
            <!-- Характеристики -->
            <div v-if="ad.features && ad.features.length > 0" class="flex flex-wrap gap-1 mb-3">
                <Badge 
                    v-for="feature in ad.features.slice(0, 2)" 
                    :key="feature" 
                    variant="outline" 
                    class="text-xs px-2 py-0.5"
                >
                    {{ feature }}
                </Badge>
                <span v-if="ad.features.length > 2" class="text-xs text-gray-500">
                    +{{ ad.features.length - 2 }}
                </span>
            </div>
            
            <Link :href="route('user.ads.edit', [ad.user_id, ad.id])" class="text-blue-500 text-sm hover:underline">
                Редактировать
            </Link>
        </div>
    </div>
</template>
