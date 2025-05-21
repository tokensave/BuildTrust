<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import type { Ad, Company, User } from '@/types';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import AdGalleryModal from '@/components/AdGalleryModal.vue';
import CompanyCard from '@/components/company/CompanyCard.vue';

const page = usePage<{ ad: Ad & { user: User & { company?: Company } } }>();
const ad = page.props.ad;
const company = ad.user?.company;

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

const handleKeydown = (e: KeyboardEvent) => {
    if (!isModalOpen.value) return;

    if (e.key === 'ArrowRight') nextImage();
    else if (e.key === 'ArrowLeft') prevImage();
    else if (e.key === 'Escape') closeModal();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});

</script>

<template>

    <AppLayout :breadcrumbs="[
        { title: 'Главная', href: route('dashboard') },
        { title: ad.title, href: '#' }
    ]">
        <div class="p-6 space-y-4">

            <h1 class="text-2xl font-bold">{{ ad.title }}</h1>
            <div class="text-muted-foreground">{{ ad.description }}</div>
            <div class="text-xl font-semibold">{{ ad.price }} ₽</div>

            <div class="flex flex-wrap gap-4 mt-4">
                <img
                    v-for="(image, index) in ad.media"
                    :key="image.id"
                    :src="image.original_url"
                    alt="Изображение"
                    class="w-48 h-32 object-cover rounded border cursor-pointer"
                    @click="openModal(ad.media.map(m => m.original_url), index)"
                />
            </div>

            <!-- 👇 Блок с компанией -->
            <div v-if="company" class="mt-12">
                <h2 class="text-2xl font-semibold mb-4">Информация о компании</h2>
                <CompanyCard :company="company" :user="ad.user" />
            </div>

        </div>

        <AdGalleryModal
            :images="modalImages"
            :index="modalIndex"
            @close="closeModal"
            @next="nextImage"
            @prev="prevImage"
        />
    </AppLayout>
</template>

