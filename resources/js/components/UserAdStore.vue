<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    ad: {
        id: number;
        title: string;
        description?: string;
        media?: any[];
    };
}>();

const image = props.ad.media?.[0]?.original_url ?? 'https://via.placeholder.com/400x200?text=Нет+изображения';
</script>

<template>
    <div class="rounded-xl shadow hover:shadow-md transition overflow-hidden bg-white dark:bg-gray-900">
        <img :src="image" class="w-full h-48 object-cover" alt="Ad Image" />
        <div class="p-4">
            <h3 class="font-semibold text-lg mb-1 truncate">{{ ad.title }}</h3>
            <p class="text-sm text-gray-500 line-clamp-2">{{ ad.description }}</p>
            <Link :href="route('user.ads.edit', [ad.user_id, ad.id])" class="text-blue-500 text-sm mt-2 inline-block">Редактировать</Link>
        </div>
    </div>
</template>
