<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Table, TableRow, TableCell, TableHeader, TableHead, TableBody } from '@/components/ui/table';
import { ref } from 'vue';
import AdGalleryModal from '@/components/AdGalleryModal.vue';
import type { Ad, SharedData, User } from '@/types';

const page = usePage<SharedData & { ads: Ad[] }>();
const ads = page.props.ads;
const user = page.props.auth.user as User;

const modalIndex = ref<number | null>(null);
const modalImages = ref<string[]>([]);
const isModalOpen = ref(false);

const openModal = (images: string[], index = 0) => {
    modalImages.value = images.length > 0 ? images : ['/default.jpg'];
    modalIndex.value = index;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    modalIndex.value = null;
};

const nextImage = () => {
    if (modalIndex.value !== null && modalIndex.value < modalImages.value.length - 1) {
        modalIndex.value++;
    }
};

const prevImage = () => {
    if (modalIndex.value !== null && modalIndex.value > 0) {
        modalIndex.value--;
    }
};
</script>

<template>
    <Head title="Главная" />
    <AppLayout :breadcrumbs="[{ title: 'Главная', href: '/dashboard' }]">
        <div class="p-4">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead></TableHead>
                        <TableHead>Информация</TableHead>
                        <TableHead class="text-right">Цена</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="ad in ads" :key="ad.id">
                        <TableCell class="w-48">
                            <img
                                :src="ad.media?.[0]?.original_url ?? '/default.jpg'"
                                alt="Изображение объявления"
                                class="h-32 w-48 rounded object-cover cursor-pointer"
                                @click.stop.prevent="openModal(ad.media?.map(m => m.original_url) ?? ['/default.jpg'], 0)"
                            />
                        </TableCell>
                        <TableCell>
                            <div
                                class="font-semibold cursor-pointer hover:underline"
                                @click="$inertia.visit(route('user.ads.show', [user.id, ad.id]))"
                            >
                                {{ ad.title }}
                            </div>
                            <div
                                class="text-sm text-muted-foreground cursor-pointer hover:underline"
                                @click="$inertia.visit(route('user.ads.show', [ad.user_id, ad.id]))"
                            >
                                {{ ad.description }}
                            </div>
                        </TableCell>
                        <TableCell class="text-right font-medium">{{ ad.price }} ₽</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>

    <AdGalleryModal
        :images="modalImages"
        :index="modalIndex"
        @close="closeModal"
        @next="nextImage"
        @prev="prevImage"
    />
</template>
