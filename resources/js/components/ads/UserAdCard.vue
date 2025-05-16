<!-- src/components/UserAdCard.vue -->
<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Card,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Trash2 } from 'lucide-vue-next';

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
        case 'draft':
            return 'Черновик';
        case 'published':
            return 'Опубликовано';
        case 'archived':
            return 'Архив';
        default:
            return 'Неизвестно';
    }
}

function statusColorClass(status: string): string {
    switch (status) {
        case 'draft':
            return 'bg-yellow-200 text-yellow-800';
        case 'published':
            return 'bg-green-200 text-green-800';
        case 'archived':
            return 'bg-gray-300 text-gray-800';
        default:
            return 'bg-red-200 text-red-800';
    }
}

function deleteAd() {
    if (confirm('Вы уверены, что хотите удалить объявление?')) {
        router.delete(route('user.ads.destroy', [props.ad.user_id, props.ad.id]));
    }
}
</script>

<template>
    <Card class="overflow-hidden group p-0 relative">
        <button
            @click.stop="deleteAd"
            class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity"
            title="Удалить"
        >
            <Trash2 class="w-4 h-4 text-red-600" />
        </button>

        <Link
            :href="route('user.ads.edit', [props.ad.user_id, props.ad.id])"
            class="block relative"
        >
            <img
                :src="props.ad.image_url"
                alt="Preview"
                class="object-cover w-full h-48 transition-transform duration-300 group-hover:scale-105"
            />

            <!-- Статус -->
            <div
                :class="[
          'absolute top-2 left-2 text-xs font-semibold rounded px-2 py-1 shadow z-10',
          statusColorClass(props.ad.status),
        ]"
            >
                {{ statusLabel(props.ad.status) }}
            </div>

            <!-- Текст -->
            <div
                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity p-4 flex flex-col justify-end text-white"
            >
                <CardTitle>{{ props.ad.title }}</CardTitle>
                <CardDescription>{{ props.ad.description }}</CardDescription>
            </div>
        </Link>
    </Card>
</template>

