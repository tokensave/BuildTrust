<script setup lang="ts">
import { Card, CardDescription, CardTitle } from '@/components/ui/card';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import type { SharedData, User } from '@/types';
import { toast } from 'vue-sonner';
import FeaturesDisplay from '@/components/FeaturesDisplay.vue';

// Получаем авторизованного пользователя из глобальных пропсов
const page = usePage<SharedData>();
const user = page.props.auth.user as User;

// Получаем конкретное объявление через пропсы
const props = defineProps<{
    ad: {
        id: number;
        title: string;
        type: string;
        category?: string;
        subcategory?: string;
        location?: string;
        description: string;
        price?: number;
        is_urgent: boolean;
        features?: string[];
        image_url: string;
        user_id: number;
        status: string;
        formatted_category?: string;
        is_service: boolean;
    };
}>();

function statusLabel(status: string) {
    switch (status) {
        case 'draft': return 'Черновик';
        case 'published': return 'Опубликовано';
        case 'archived': return 'Архив';
        default: return 'Неизвестно';
    }
}

function statusColorClass(status: string): string {
    switch (status) {
        case 'draft': return 'bg-yellow-200 text-yellow-800';
        case 'published': return 'bg-green-200 text-green-800';
        case 'archived': return 'bg-gray-300 text-gray-800';
        default: return 'bg-red-200 text-red-800';
    }
}

// Удаление объявления
function deleteAd() {
    if (confirm('Вы уверены, что хотите удалить объявление?')) {
        router.delete(route('user.ads.destroy', { user: user.id, ad: props.ad.id }), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['ads'] });
            },
            onError: () => {
                toast.error('Невозможно удалить объявление при наличии незакрытых сделок.')
            }
        });
    }
}
</script>

<template>
    <Card class="group relative overflow-hidden p-0">
        <button
            @click.stop="deleteAd"
            class="absolute top-2 right-2 z-10 opacity-0 transition-opacity group-hover:opacity-100"
            title="Удалить"
        >
            <Trash2 class="h-4 w-4 text-red-600" />
        </button>

        <Link
            :href="route('user.ads.edit', { user: user.id, ad: props.ad.id })"
            class="relative block"
        >
            <img
                :src="props.ad.image_url"
                alt="Preview"
                class="h-48 w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />

            <!-- Статус -->
            <div
                :class="[
          'absolute top-2 left-2 z-10 rounded px-2 py-1 text-xs font-semibold shadow',
          statusColorClass(props.ad.status)
        ]"
            >
                {{ statusLabel(props.ad.status) }}
            </div>

            <!-- Бейджи сверху справа -->
            <div class="absolute top-2 right-8 z-10 flex gap-1">
                <span v-if="props.ad.is_urgent" class="bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                    Срочно
                </span>
                <span v-if="props.ad.is_service" class="bg-blue-500 text-white px-2 py-1 rounded text-xs font-semibold">
                    Услуга
                </span>
            </div>

            <!-- Информация при наведении -->
            <div class="absolute inset-0 flex flex-col justify-end bg-black/50 p-4 text-white opacity-0 transition-opacity group-hover:opacity-100">
                <CardTitle class="mb-2">{{ props.ad.title }}</CardTitle>
                
                <!-- Категории -->
                <div v-if="props.ad.formatted_category" class="text-xs text-gray-300 mb-1">
                    {{ props.ad.formatted_category }}
                </div>
                
                <!-- Местоположение и цена -->
                <div class="flex justify-between items-center mb-2 text-sm">
                    <span v-if="props.ad.location" class="text-gray-300">
                        📍 {{ props.ad.location }}
                    </span>
                    <span v-if="props.ad.price" class="font-semibold text-green-300">
                        {{ props.ad.price }} ₽
                    </span>
                    <span v-else-if="!props.ad.price && !props.ad.is_service" class="font-semibold text-gray-300">
                        Договорная
                    </span>
                </div>
                
                <CardDescription class="text-gray-200 text-sm">
                    {{ props.ad.description }}
                </CardDescription>
                
                <!-- Характеристики -->
                <div class="mt-2">
                    <div v-if="props.ad.features && props.ad.features.length > 0" class="flex flex-wrap gap-1">
                        <span 
                            v-for="feature in props.ad.features.slice(0, 3)" 
                            :key="feature"
                            class="bg-white/20 text-white px-2 py-0.5 rounded text-xs"
                        >
                            {{ feature }}
                        </span>
                        <span v-if="props.ad.features.length > 3" class="text-xs text-gray-300">
                            +{{ props.ad.features.length - 3 }}
                        </span>
                    </div>
                </div>
            </div>
        </Link>
    </Card>
</template>
