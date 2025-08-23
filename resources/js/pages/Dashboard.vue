<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Table, TableRow, TableCell, TableHeader, TableHead, TableBody } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { computed, ref } from 'vue';
import AdGalleryModal from '@/components/ads/AdGalleryModal.vue';
import FeaturesDisplay from '@/components/ads/FeaturesDisplay.vue';
import AdFilters from '@/components/ads/AdFilters.vue';
import type { Ad, SharedData, User, AdFilter, Paginated } from '@/types';
import AdPagination from '@/components/ads/AdPagination.vue';
import { truncateText } from '@/composables/useInitials';

const page = usePage<SharedData & { ads: Paginated<Ad>, filters?: AdFilter }>(); // Используем общий тип
const ads = computed(() => page.props.ads.data); // Теперь берем data из пагинации
const pagination = computed(() => ({
    currentPage: page.props.ads.current_page,
    lastPage: page.props.ads.last_page,
    perPage: page.props.ads.per_page,
    total: page.props.ads.total,
    from: page.props.ads.from,
    to: page.props.ads.to,
}));
const user = computed(() => page.props.auth.user as User); // ✅ Реактивно
const filters = computed(() => page.props.filters || {});

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

const handlePageChange = (pageNumber: number) => {
    const params = {
        ...filters.value,
        page: pageNumber,
    };

    router.get('/dashboard', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Главная" />
    <AppLayout :breadcrumbs="[{ title: 'Главная', href: '/dashboard' }]">
        <div class="p-4">
            <!-- Компонент фильтров -->
            <AdFilters
                :filters="filters"
                :total-count="pagination.total"
            />

            <!-- Таблица объявлений -->
            <div v-if="ads.length > 0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-48">Изображение</TableHead>
                            <TableHead>Информация</TableHead>
                            <TableHead class="w-32">Тип</TableHead>
                            <TableHead class="w-32">Город</TableHead>
                            <TableHead class="text-right w-32">Цена</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="ad in ads" :key="ad.id" class="hover:bg-muted/50">
                            <TableCell class="p-2">
                                <div class="relative">
                                    <img
                                        :src="ad.media?.[0]?.original_url ?? '/default.jpg'"
                                        alt="Изображение объявления"
                                        class="h-24 w-40 rounded-lg object-cover cursor-pointer"
                                        @click.stop.prevent="openModal(ad.media?.map(m => m.original_url) ?? ['/default.jpg'], 0)"
                                    />
                                    <!-- Бейдж "Срочно" -->
                                    <Badge
                                        v-if="ad.is_urgent"
                                        variant="destructive"
                                        class="absolute top-1 left-1 text-xs px-1 py-0.5"
                                    >
                                        Срочно
                                    </Badge>
                                    <!-- Бейдж для услуг -->
                                    <Badge
                                        v-if="ad.is_service"
                                        variant="secondary"
                                        class="absolute top-1 right-1 text-xs px-1 py-0.5"
                                    >
                                        Услуга
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell class="space-y-1">
                                <div
                                    class="font-semibold cursor-pointer hover:underline text-base"
                                    @click="$inertia.visit(route('user.ads.show', [user.id, ad.id]))"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                >
                                    {{ ad.title }}
                                </div>

                                <!-- Категории -->
                                <div v-if="ad.formatted_category" class="text-xs text-muted-foreground">
                                    {{ ad.formatted_category }}
                                </div>

                                <div
                                    class="text-sm text-muted-foreground cursor-pointer hover:underline"
                                    @click="$inertia.visit(route('user.ads.show', [user.id, ad.id]))"
                                    style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"
                                >
                                    {{ truncateText(ad.description, 50) }}
                                </div>

                                <!-- Дополнительные характеристики -->
                                <div class="mt-2">
                                    <FeaturesDisplay
                                        :features="ad.features"
                                        :max-visible="3"
                                        size="sm"
                                    />
                                </div>

                                <!-- Дата создания -->
                                <div class="text-xs text-muted-foreground mt-1">
                                    {{ new Date(ad.created_at).toLocaleDateString('ru-RU') }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary" class="text-xs">
                                    {{ ad.is_service ? 'Услуга' : 'Товар' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ ad.location || '—' }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="font-semibold text-lg">
                                    {{ ad.price ? `${ad.price} ₽` : 'Договорная' }}
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Пагинация -->
                <AdPagination
                    v-if="pagination.lastPage > 1"
                    :current-page="pagination.currentPage"
                    :last-page="pagination.lastPage"
                    :total="pagination.total"
                    :from="pagination.from"
                    :to="pagination.to"
                    :filters="filters"
                    @change-page="handlePageChange"
                />
            </div>

            <!-- Пустое состояние -->
            <div v-else class="text-center py-12">
                <div class="text-muted-foreground mb-4">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Объявления не найдены
                </div>
                <p class="text-sm text-muted-foreground">
                    Попробуйте изменить фильтры поиска или
                    <button
                        @click="$inertia.visit('/dashboard')"
                        class="text-primary hover:underline"
                    >
                        сбросить все фильтры
                    </button>
                </p>
            </div>
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
