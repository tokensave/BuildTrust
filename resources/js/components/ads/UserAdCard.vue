<script setup lang="ts">
import { Card, CardDescription, CardTitle } from '@/components/ui/card';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import type { SharedData, User } from '@/types';
import { toast } from 'vue-sonner';

// Получаем авторизованного пользователя из глобальных пропсов
const page = usePage<SharedData>();
const user = page.props.auth.user as User;

// Получаем конкретное объявление через пропсы
const props = defineProps<{
    ad: {
        id: number;
        title: string;
        description: string;
        image_url: string;
        user_id: number;
        status: string;
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

            <div
                :class="[
          'absolute top-2 left-2 z-10 rounded px-2 py-1 text-xs font-semibold shadow',
          statusColorClass(props.ad.status)
        ]"
            >
                {{ statusLabel(props.ad.status) }}
            </div>

            <div class="absolute inset-0 flex flex-col justify-end bg-black/50 p-4 text-white opacity-0 transition-opacity group-hover:opacity-100">
                <CardTitle>{{ props.ad.title }}</CardTitle>
                <CardDescription class="text-gray-200">{{ props.ad.description }}</CardDescription>
            </div>
        </Link>
    </Card>
</template>
